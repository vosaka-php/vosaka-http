<?php

declare(strict_types=1);

namespace vosaka\http\server;

use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use vosaka\foroutines\AsyncIO;
use vosaka\foroutines\Pause;
use vosaka\http\exceptions\HttpException;
use vosaka\http\message\ServerRequest;
use vosaka\http\message\Stream;
use vosaka\http\message\Uri;

/**
 * HttpRequestParser — vosaka-foroutines (Fiber + AsyncIO).
 *
 * Replaces the old generator-based TCPConnection approach with direct
 * AsyncIO socket reads. All I/O suspends the current Fiber via
 * AsyncIO's waitForRead() so the scheduler can run other fibers.
 *
 * Caller must be inside a Fiber (Launch::new / Async::new).
 *
 *   $parser  = new HttpRequestParser($config);
 *   $request = $parser->parseRequest($socket);   // suspends fiber during I/O
 */
final class HttpRequestParser
{
    /**
     * Maximum single read per chunk — matches AsyncIO::READ_CHUNK_SIZE.
     */
    private const READ_CHUNK = 65536;

    /**
     * Hard limit for header section (8 KB).
     * Protects against header-flooding attacks.
     */
    private const MAX_HEADER_BYTES = 8192;

    public function __construct(private readonly ServerConfig $config) {}

    // ── Public entry point ───────────────────────────────────────────────────

    /**
     * Parse a complete HTTP/1.x request from a raw non-blocking socket.
     *
     * Suspends the fiber while waiting for data — no blocking, no generators.
     *
     * @param  resource $socket  Non-blocking client socket.
     * @return ServerRequestInterface|null  null on empty / closed connection.
     * @throws HttpException  On protocol violations (400, 413, etc.).
     * @throws RuntimeException  On I/O errors.
     */
    public function parseRequest(mixed $socket): ?ServerRequestInterface
    {
        // ── 1. Read everything up to end-of-headers (\r\n\r\n) ──────────────
        $headerSection = $this->readUntilBlankLine($socket);
        if ($headerSection === '') {
            return null; // peer closed connection before sending anything
        }

        // ── 2. Split status line + headers ──────────────────────────────────
        $crlfPos = strpos($headerSection, "\r\n");
        if ($crlfPos === false) {
            throw new HttpException('Invalid HTTP request: missing request line', 400);
        }

        $requestLine = substr($headerSection, 0, $crlfPos);
        $headerRaw   = substr($headerSection, $crlfPos + 2);

        // ── 3. Parse request line ────────────────────────────────────────────
        $parsed = $this->parseRequestLine($requestLine);
        if ($parsed === null) {
            throw new HttpException('Invalid HTTP request line', 400);
        }
        [$method, $target, $version] = $parsed;

        // ── 4. Parse headers ─────────────────────────────────────────────────
        $headers = $this->parseHeaders($headerRaw);

        // ── 5. Read body (if any) ────────────────────────────────────────────
        $body = $this->readBody($socket, $headers);

        // ── 6. Build PSR-7 ServerRequest ─────────────────────────────────────
        $uri          = $this->parseUri($target);
        $serverParams = $this->buildServerParams($method, $target, $version);

        $request = new ServerRequest(
            $method,
            $uri,
            $headers,
            $body,
            $version,
            $serverParams,
        );

        return $this->enrichRequest($request, $uri, $body->getContents());
    }

    // ── I/O primitives (AsyncIO-based) ───────────────────────────────────────

    /**
     * Read raw bytes from the socket until the HTTP header terminator
     * "\r\n\r\n" is found, then return everything up to and including it
     * (without the terminator itself — callers split on "\r\n").
     *
     * Uses AsyncIO::streamRead()->await() which suspends the Fiber via
     * waitForRead() + Fiber::suspend() — zero blocking.
     *
     * @param  resource $socket
     * @return string  Raw header section, empty string on closed connection.
     * @throws HttpException  If headers exceed MAX_HEADER_BYTES.
     */
    private function readUntilBlankLine(mixed $socket): string
    {
        $buffer     = '';
        $terminator = "\r\n\r\n";
        $emptyReads = 0;
        // Allow up to N consecutive empty reads on a keep-alive connection
        // before treating it as a closed socket. Each empty read = one
        // scheduler tick where the stream was "ready" but had no data yet.
        $maxEmptyReads = 100;

        while (true) {
            $chunk = AsyncIO::streamRead($socket, self::READ_CHUNK)->await();

            if ($chunk !== '') {
                $emptyReads = 0;
                $buffer    .= $chunk;

                if (strlen($buffer) > self::MAX_HEADER_BYTES) {
                    throw new HttpException(
                        'Request headers too large (> ' . self::MAX_HEADER_BYTES . ' bytes)',
                        431,
                    );
                }

                $pos = strpos($buffer, $terminator);
                if ($pos !== false) {
                    return substr($buffer, 0, $pos);
                }
                continue;
            }

            // chunk === "" — either EOF or stream temporarily not ready
            if (!is_resource($socket) || feof($socket)) {
                // True EOF — client closed connection
                return '';
            }

            // Stream was signalled ready but fread returned nothing.
            // On a keep-alive connection this means the client hasn't sent
            // the next request yet. Yield and retry.
            if (++$emptyReads >= $maxEmptyReads) {
                return ''; // give up — treat as closed
            }

            Pause::force();
        }
    }

    /**
     * Read exactly $length bytes from the socket for the request body.
     *
     * Accumulates chunks via AsyncIO::streamRead()->await() until the full
     * Content-Length is satisfied.
     *
     * @param  resource $socket
     * @param  int      $length  Exact byte count to read.
     * @return string
     * @throws HttpException  On premature EOF.
     */
    private function readExact(mixed $socket, int $length): string
    {
        $buffer    = '';
        $remaining = $length;

        while ($remaining > 0) {
            $chunk = AsyncIO::streamRead(
                $socket,
                min($remaining, self::READ_CHUNK),
            )->await();

            if ($chunk === '') {
                throw new HttpException(
                    "Connection closed after " . strlen($buffer) . "/{$length} body bytes",
                    400,
                );
            }

            $buffer    .= $chunk;
            $remaining -= strlen($chunk);
        }

        return $buffer;
    }

    // ── Body reader ──────────────────────────────────────────────────────────

    /**
     * @param  resource $socket
     * @param  array<string, list<string>> $headers
     */
    private function readBody(mixed $socket, array $headers): Stream
    {
        $contentLength = (int) ($headers['content-length'][0] ?? 0);

        if ($contentLength === 0) {
            return Stream::create('');
        }

        if ($contentLength > $this->config->maxRequestSize) {
            throw new HttpException(
                "Request body too large ({$contentLength} > {$this->config->maxRequestSize})",
                413,
            );
        }

        return Stream::create($this->readExact($socket, $contentLength));
    }

    // ── Parsing helpers ──────────────────────────────────────────────────────

    /**
     * @return array{0:string, 1:string, 2:string}|null  [method, target, version]
     */
    private function parseRequestLine(string $line): ?array
    {
        $parts = explode(' ', rtrim($line), 3);
        if (count($parts) !== 3) {
            return null;
        }

        [$method, $target, $proto] = $parts;

        // Fast validation — method must be all alpha, protocol must start with HTTP/
        if (!ctype_alpha($method) || !str_starts_with($proto, 'HTTP/')) {
            return null;
        }

        return [$method, $target, substr($proto, 5)]; // strip "HTTP/" prefix
    }

    /**
     * Parse the raw header block (everything after the request line)
     * into a PSR-7-style map: lowercase-name => [value, ...].
     *
     * @return array<string, list<string>>
     */
    private function parseHeaders(string $raw): array
    {
        $headers = [];

        foreach (explode("\r\n", $raw) as $line) {
            if ($line === '') {
                continue;
            }
            $colonPos = strpos($line, ':');
            if ($colonPos === false) {
                continue; // malformed header line — skip silently
            }
            $name            = strtolower(trim(substr($line, 0, $colonPos)));
            $value           = trim(substr($line, $colonPos + 1));
            $headers[$name][] = $value;
        }

        return $headers;
    }

    private function parseUri(string $target): Uri
    {
        // Relative URI (the common case) — prepend a dummy base so parse_url works
        if ($target !== '' && $target[0] === '/') {
            return new Uri('http://localhost' . $target);
        }
        return new Uri($target);
    }

    private function buildServerParams(
        string $method,
        string $target,
        string $version,
    ): array {
        // Cache time() to avoid a syscall on every request
        static $cachedTime      = 0;
        static $cachedTimeFloat = 0.0;
        static $lastUpdate      = 0;

        $now = time();
        if ($now !== $lastUpdate) {
            $cachedTime      = $now;
            $cachedTimeFloat = microtime(true);
            $lastUpdate      = $now;
        }

        return [
            'REQUEST_METHOD'     => $method,
            'REQUEST_URI'        => $target,
            'SERVER_PROTOCOL'    => "HTTP/{$version}",
            'REQUEST_TIME'       => $cachedTime,
            'REQUEST_TIME_FLOAT' => $cachedTimeFloat,
        ];
    }

    // ── Request enrichment ───────────────────────────────────────────────────

    private function enrichRequest(
        ServerRequestInterface $request,
        Uri $uri,
        string $body,
    ): ServerRequestInterface {
        // Query string
        $query = $uri->getQuery();
        if ($query !== '') {
            $queryParams = [];
            parse_str($query, $queryParams);
            $request = $request->withQueryParams($queryParams);
        }

        // Fast path — GET / HEAD / DELETE carry no body
        $method = $request->getMethod();
        if ($method === 'GET' || $method === 'HEAD' || $method === 'DELETE') {
            return $request;
        }

        // Parse form body for POST/PUT/PATCH
        if ($body !== '' && $this->isFormEncoded($request)) {
            $parsed = [];
            parse_str($body, $parsed);
            $request = $request->withParsedBody($parsed);
        }

        return $request;
    }

    private function isFormEncoded(ServerRequestInterface $request): bool
    {
        return str_contains(
            $request->getHeaderLine('content-type'),
            'application/x-www-form-urlencoded',
        );
    }
}
