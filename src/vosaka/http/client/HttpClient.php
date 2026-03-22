<?php

declare(strict_types=1);

namespace vosaka\http\client;

use CurlHandle;
use CurlMultiHandle;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use Psr\Http\Message\RequestInterface;
use vosaka\foroutines\Deferred;
use vosaka\foroutines\LazyDeferred;
use vosaka\foroutines\Launch;
use vosaka\foroutines\Pause;
use vosaka\foroutines\channel\Channel;
use vosaka\http\message\Request;
use vosaka\http\message\Response;
use vosaka\http\message\Stream;

/**
 * Asynchronous HTTP Client — vosaka-foroutines (Fiber-based).
 *
 * Architecture: One shared driver fiber manages the cURL multi handle.
 * Each request gets its own Channel(1) for result delivery.
 * Callers suspend on channel->receive() — they never touch the multi handle.
 *
 *   enqueue(url)
 *     ├─ curl_multi_add_handle(multiHandle, ch)
 *     ├─ pendingChannels[(int)$ch] = Channel::new(1)
 *     ├─ ensureDriverRunning()          (no-op if driver already live)
 *     └─ return LazyDeferred::new(fn => channel->receive())   ← suspends here
 *
 *   Driver fiber (1 global instance):
 *     loop:
 *       curl_multi_exec(multiHandle, $active)   // non-blocking, timeout=0
 *       curl_multi_info_read → channel[id]->send(result)
 *       if no pending → exit
 *       Pause::new()                             // yield to scheduler
 */
final class HttpClient
{
    // ── Defaults ─────────────────────────────────────────────────────────────

    private array $defaultOptions = [
        'timeout'          => 30,
        'connect_timeout'  => 10,
        'follow_redirects' => true,
        'max_redirects'    => 5,
        'ssl_verify'       => false,
        'ssl_ca_bundle'    => null,
        'ssl_cert'         => null,
        'ssl_key'          => null,
        'user_agent'       => 'VOsaka-HTTP/2.0',
        'headers'          => [],
        'proxy'            => null,
        'cookies'          => null,
    ];

    // ── Shared state (all HttpClient instances share one multi handle) ────────

    private static ?CurlMultiHandle $multiHandle   = null;

    /**
     * Map: (int)$curlHandle → Channel<Response|Throwable>
     * Only the driver fiber writes to these channels.
     * Only the corresponding waiter fiber reads from them.
     *
     * @var array<int, Channel>
     */
    private static array $pendingChannels = [];

    /** True while the driver fiber is alive. */
    private static bool $driverRunning = false;

    // ── Constructor ──────────────────────────────────────────────────────────

    public function __construct(array $options = [])
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);

        if ($this->defaultOptions['ssl_verify'] && $this->defaultOptions['ssl_ca_bundle'] === null) {
            $this->defaultOptions['ssl_ca_bundle'] = self::detectCABundle();
        }

        self::initMultiHandle();
    }

    // ── Public request API ───────────────────────────────────────────────────

    /**
     * Send any PSR-7 request asynchronously.
     * Returns a Deferred that resolves to a Response.
     *
     * Usage:
     *   $response = $client->send($request)->await();
     *   // or concurrently:
     *   [$r1, $r2] = [$client->get($url1)->await(), $client->get($url2)->await()];
     */
    public function send(RequestInterface $request, array $options = []): Deferred
    {
        $options = array_merge($this->defaultOptions, $options);
        $this->validateUri($request->getUri());

        $ch = $this->buildCurlHandle($request, $options);
        return self::enqueue($ch);
    }

    public function get(string $url, array $headers = [], array $options = []): Deferred
    {
        return $this->send(new Request('GET', $url, $headers), $options);
    }

    public function post(string $url, mixed $body = null, array $headers = [], array $options = []): Deferred
    {
        $stream  = $this->prepareBody($body, $headers);
        return $this->send(new Request('POST', $url, $headers, $stream), $options);
    }

    public function put(string $url, mixed $body = null, array $headers = [], array $options = []): Deferred
    {
        $stream = $this->prepareBody($body, $headers);
        return $this->send(new Request('PUT', $url, $headers, $stream), $options);
    }

    public function patch(string $url, mixed $body = null, array $headers = [], array $options = []): Deferred
    {
        $stream = $this->prepareBody($body, $headers);
        return $this->send(new Request('PATCH', $url, $headers, $stream), $options);
    }

    public function delete(string $url, array $headers = [], array $options = []): Deferred
    {
        return $this->send(new Request('DELETE', $url, $headers), $options);
    }

    public function head(string $url, array $headers = [], array $options = []): Deferred
    {
        return $this->send(new Request('HEAD', $url, $headers), $options);
    }

    // ── Core: enqueue + driver ────────────────────────────────────────────────

    /**
     * Register a ready-configured cURL easy handle into the multi handle,
     * attach a result Channel, and return a LazyDeferred that suspends until done.
     */
    private static function enqueue(CurlHandle $ch): LazyDeferred
    {
        $code = curl_multi_add_handle(self::$multiHandle, $ch);
        if ($code !== CURLM_OK) {
            curl_close($ch);
            throw new RuntimeException(
                'curl_multi_add_handle failed: ' . curl_multi_strerror($code)
            );
        }

        // One buffered slot — driver sends exactly once, waiter receives once.
        $channel = Channel::new(capacity: 1);
        self::$pendingChannels[(int) $ch] = $channel;

        self::ensureDriverRunning();

        // This LazyDeferred suspends the caller's Fiber at Pause::force() when awaited.
        // It does NOT touch the multi handle — that's the driver's job.
        //
        // NOTE: We use tryReceive() + Pause::force() instead of receive()
        // to avoid being prematurely resumed by the scheduler's blind tick.
        // If we used receive(), the scheduler would resume us with null,
        // and if the driver then resumed us with the real Response at
        // the wrong suspension point (Pause::force), the response would be lost.
        return new LazyDeferred(static function () use ($channel, $ch): Response {
            $result = null;
            while ($result === null) {
                $result = $channel->tryReceive();
                if ($result === null) {
                    Pause::force(); // yield to scheduler, let driver run
                }
            }

            // Driver always sends either a Response or a Throwable.
            if ($result instanceof Throwable) {
                throw $result;
            }

            return $result;
        });
    }

    /**
     * Launch the driver fiber if not already running.
     * The driver is the sole owner of curl_multi_exec / curl_multi_info_read.
     */
    private static function ensureDriverRunning(): void
    {
        if (self::$driverRunning) {
            return;
        }

        self::$driverRunning = true;

        Launch::new(static function (): void {
            try {
                self::runDriver();
            } finally {
                // Guard: if driver exits with channels still pending (crash),
                // send errors so waiters don't block forever.
                foreach (self::$pendingChannels as $id => $channel) {
                    $channel->send(new RuntimeException(
                        "HTTP driver terminated unexpectedly (handle id: $id)"
                    ));
                }
                self::$pendingChannels = [];
                self::$driverRunning   = false;
            }
        });
    }

    /**
     * The actual driver loop.
     * Runs until all pending channels are delivered.
     * Never blocks — uses Pause::new() to yield between ticks.
     */
    private static function runDriver(): void
    {
        while (!empty(self::$pendingChannels)) {
            // Tick the multi handle — completely non-blocking.
            $active = 0;
            $status = curl_multi_exec(self::$multiHandle, $active);

            if ($status !== CURLM_OK && $status !== CURLM_CALL_MULTI_PERFORM) {
                // Multi handle is broken — fail all pending requests.
                $error = curl_multi_strerror($status);
                foreach (self::$pendingChannels as $channel) {
                    $channel->send(new RuntimeException("curl_multi_exec: $error"));
                }
                self::$pendingChannels = [];
                return;
            }

            // Harvest completed transfers.
            while ($info = curl_multi_info_read(self::$multiHandle)) {
                $ch      = $info['handle'];
                $id      = (int) $ch;
                $channel = self::$pendingChannels[$id] ?? null;

                if ($channel === null) {
                    curl_multi_remove_handle(self::$multiHandle, $ch);
                    curl_close($ch);
                    continue;
                }

                if ($info['result'] !== CURLE_OK) {
                    $channel->send(new RuntimeException(
                        'cURL request failed: ' . curl_error($ch)
                            . ' (' . curl_strerror($info['result']) . ')'
                    ));
                } else {
                    try {
                        $channel->send(self::parseResponse($ch));
                    } catch (Throwable $e) {
                        $channel->send($e);
                    }
                }

                curl_multi_remove_handle(self::$multiHandle, $ch);
                curl_close($ch);
                unset(self::$pendingChannels[$id]);
            }

            // Yield to the scheduler so waiter fibers can run.
            // Using Pause::force() to guarantee a yield even if batching is enabled.
            Pause::force();
        }
    }

    // ── cURL handle construction ─────────────────────────────────────────────

    private function buildCurlHandle(RequestInterface $request, array $options): CurlHandle
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('curl_init() failed');
        }

        $uri = $request->getUri();

        curl_setopt_array($ch, [
            CURLOPT_URL            => (string) $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_CUSTOMREQUEST  => $request->getMethod(),
            CURLOPT_TIMEOUT        => $options['timeout'],
            CURLOPT_CONNECTTIMEOUT => $options['connect_timeout'],
            CURLOPT_FOLLOWLOCATION => $options['follow_redirects'],
            CURLOPT_MAXREDIRS      => $options['max_redirects'],
            CURLOPT_SSL_VERIFYPEER => $options['ssl_verify'],
            CURLOPT_SSL_VERIFYHOST => $options['ssl_verify'] ? 2 : 0,
            CURLOPT_USERAGENT      => $options['user_agent'],
        ]);

        if ($options['ssl_ca_bundle'] !== null) {
            curl_setopt($ch, CURLOPT_CAINFO, $options['ssl_ca_bundle']);
        }
        if ($options['ssl_cert'] !== null) {
            curl_setopt($ch, CURLOPT_SSLCERT, $options['ssl_cert']);
        }
        if ($options['ssl_key'] !== null) {
            curl_setopt($ch, CURLOPT_SSLKEY, $options['ssl_key']);
        }
        if ($options['proxy'] !== null) {
            curl_setopt($ch, CURLOPT_PROXY, $options['proxy']);
        }
        if ($options['cookies'] !== null) {
            curl_setopt($ch, CURLOPT_COOKIE, $options['cookies']);
        }

        $headers = $this->buildHeaderLines($request, $options);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $body = $request->getBody();
        if ($body->getSize() > 0) {
            $body->rewind();
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body->getContents());
        }

        return $ch;
    }

    private function buildHeaderLines(RequestInterface $request, array $options): array
    {
        $merged = array_merge(
            ['Accept' => '*/*'],
            $options['headers'],
            $request->getHeaders(),
        );

        $lines = [];
        foreach ($merged as $name => $values) {
            foreach ((array) $values as $value) {
                $lines[] = "$name: $value";
            }
        }
        return $lines;
    }

    // ── Response parsing ─────────────────────────────────────────────────────

    private static function parseResponse(CurlHandle $ch): Response
    {
        $raw = curl_multi_getcontent($ch);
        if ($raw === false || $raw === null) {
            throw new RuntimeException('curl_multi_getcontent() returned no data');
        }

        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerRaw  = substr($raw, 0, $headerSize);
        $bodyRaw    = substr($raw, $headerSize);

        $headers         = [];
        $reasonPhrase    = '';
        $protocolVersion = '1.1';

        foreach (explode("\r\n", trim($headerRaw)) as $i => $line) {
            if ($i === 0) {
                if (preg_match('/^HTTP\/(\d\.\d)\s+\d{3}\s*(.*)$/', $line, $m)) {
                    $protocolVersion = $m[1];
                    $reasonPhrase    = $m[2] ?? '';
                }
                continue;
            }
            if ($line === '' || !str_contains($line, ':')) {
                continue;
            }
            [$name, $value] = explode(':', $line, 2);
            $headers[trim($name)][] = trim($value);
        }

        return new Response($httpCode, $headers, Stream::create($bodyRaw), $protocolVersion, $reasonPhrase);
    }

    // ── Body preparation ─────────────────────────────────────────────────────

    private function prepareBody(mixed $body, array &$headers): Stream
    {
        if ($body === null) {
            return Stream::create('');
        }
        if (is_string($body)) {
            return Stream::create($body);
        }
        if (is_array($body)) {
            $headers['Content-Type'] ??= 'application/json';
            return Stream::create(json_encode($body, JSON_THROW_ON_ERROR));
        }
        if ($body instanceof Stream) {
            return $body;
        }
        throw new InvalidArgumentException('Unsupported body type: ' . get_debug_type($body));
    }

    // ── Validation ───────────────────────────────────────────────────────────

    private function validateUri(mixed $uri): void
    {
        $scheme = $uri->getScheme();
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException("Unsupported scheme '$scheme' — only http/https allowed");
        }
        if ($uri->getHost() === '') {
            throw new InvalidArgumentException('URL must include a host');
        }
    }

    // ── Lifecycle ────────────────────────────────────────────────────────────

    private static function initMultiHandle(): void
    {
        if (self::$multiHandle !== null) {
            return;
        }
        $mh = curl_multi_init();
        if ($mh === false) {
            throw new RuntimeException('curl_multi_init() failed');
        }
        // Pipelining: 0=off, 1=HTTP/1 pipeline, 2=HTTP/2 multiplex
        curl_multi_setopt($mh, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
        self::$multiHandle = $mh;
    }

    /**
     * Call this on application shutdown to release the multi handle.
     */
    public static function shutdown(): void
    {
        // Fail any still-pending channels gracefully
        foreach (self::$pendingChannels as $channel) {
            $channel->send(new RuntimeException('HttpClient::shutdown() called'));
        }
        self::$pendingChannels = [];
        self::$driverRunning   = false;

        if (self::$multiHandle !== null) {
            curl_multi_close(self::$multiHandle);
            self::$multiHandle = null;
        }
    }

    // ── CA bundle detection ──────────────────────────────────────────────────

    private static function detectCABundle(): ?string
    {
        $candidates = array_filter([
            getenv('SSL_CERT_FILE')  ?: null,
            getenv('CURL_CA_BUNDLE') ?: null,
            ini_get('curl.cainfo')   ?: null,
            ini_get('openssl.cafile') ?: null,
            '/etc/ssl/certs/ca-certificates.crt',       // Debian/Ubuntu
            '/etc/pki/tls/certs/ca-bundle.crt',         // RHEL/CentOS
            '/etc/ssl/ca-bundle.pem',                    // OpenSUSE
            '/etc/ssl/cert.pem',                         // Alpine
            '/usr/local/etc/openssl@3/cert.pem',         // macOS Homebrew
            '/opt/homebrew/etc/ca-certificates/cert.pem',
        ]);

        foreach ($candidates as $path) {
            if ($path && is_readable($path)) {
                return $path;
            }
        }

        return self::downloadCABundle();
    }

    private static function downloadCABundle(): ?string
    {
        $dir  = sys_get_temp_dir() . '/vosaka-http';
        $path = $dir . '/cacert.pem';

        if (file_exists($path) && (time() - filemtime($path)) < 30 * 86400) {
            return $path;
        }

        @mkdir($dir, 0755, true);

        $ctx     = stream_context_create(['ssl' => ['verify_peer' => false]]);
        $content = @file_get_contents('https://curl.se/ca/cacert.pem', false, $ctx);

        if ($content !== false) {
            file_put_contents($path, $content);
            return $path;
        }

        return null;
    }
}
