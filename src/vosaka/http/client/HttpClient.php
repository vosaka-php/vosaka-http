<?php

declare(strict_types=1);

namespace vosaka\http\client;

use Generator;
use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;
use vosaka\http\message\Request;
use vosaka\http\message\Response;
use vosaka\http\message\Stream;

/**
 * Asynchronous HTTP Client using cURL Multi with VOsaka runtime.
 *
 * This client provides async HTTP request capabilities using cURL Multi
 * handle for non-blocking operations. It supports GET, POST, PUT, DELETE 
 * and other HTTP methods with configurable timeouts, headers, and SSL options.
 */
final class HttpClient
{
    private array $defaultOptions = [
        "timeout" => 30,
        "connect_timeout" => 10,
        "follow_redirects" => true,
        "max_redirects" => 5,
        "ssl_verify" => false,
        "ssl_ca_bundle" => null, // Path to CA bundle file
        "ssl_cert" => null,      // Client certificate
        "ssl_key" => null,       // Client key
        "user_agent" => "VOsaka-HTTP/1.0",
        "headers" => [],
        "proxy" => null,
        "cookies" => null,
    ];

    private static mixed $multiHandle = null;
    private static array $activeHandles = [];

    public function __construct(array $options = [])
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);

        // Auto-detect CA bundle if not specified and SSL verify is enabled
        if (
            $this->defaultOptions['ssl_verify'] &&
            $this->defaultOptions['ssl_ca_bundle'] === null
        ) {
            $this->defaultOptions['ssl_ca_bundle'] = self::getDefaultCABundle();
        }

        // Initialize cURL multi handle if not exists
        if (self::$multiHandle === null) {
            self::$multiHandle = curl_multi_init();
            if (self::$multiHandle === false) {
                throw new RuntimeException("Failed to initialize cURL multi handle");
            }
        }
    }

    /**
     * Send an HTTP request asynchronously.
     */
    public function send(RequestInterface $request, array $options = []): Result
    {
        $fn = function () use ($request, $options): Generator {
            $options = array_merge($this->defaultOptions, $options);

            $uri = $request->getUri();
            $this->validateUri($uri);

            // Create cURL handle
            $ch = curl_init();
            if ($ch === false) {
                throw new RuntimeException("Failed to initialize cURL handle");
            }

            try {
                // Configure cURL options
                $this->configureCurl($ch, $request, $options);

                // Add to multi handle
                $code = curl_multi_add_handle(self::$multiHandle, $ch);
                if ($code !== CURLM_OK) {
                    throw new RuntimeException("Failed to add cURL handle to multi handle");
                }

                // Store handle reference
                $handleId = spl_object_id((object)$ch);
                self::$activeHandles[$handleId] = $ch;

                // Execute async request
                $response = yield from $this->executeAsync($ch, $handleId);

                return $response;
            } finally {
                // Cleanup
                if (isset(self::$activeHandles[$handleId])) {
                    curl_multi_remove_handle(self::$multiHandle, $ch);
                    unset(self::$activeHandles[$handleId]);
                }
                curl_close($ch);
            }
        };

        return Future::new($fn());
    }

    /**
     * Send a GET request.
     */
    public function get(
        string $url,
        array $headers = [],
        array $options = []
    ): Result {
        $request = new Request("GET", $url, $headers);
        return $this->send($request, $options);
    }

    /**
     * Send a POST request.
     */
    public function post(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = []
    ): Result {
        $stream = $this->prepareBody($body, $headers);
        $request = new Request("POST", $url, $headers, $stream);
        return $this->send($request, $options);
    }

    /**
     * Send a PUT request.
     */
    public function put(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = []
    ): Result {
        $stream = $this->prepareBody($body, $headers);
        $request = new Request("PUT", $url, $headers, $stream);
        return $this->send($request, $options);
    }

    /**
     * Send a DELETE request.
     */
    public function delete(
        string $url,
        array $headers = [],
        array $options = []
    ): Result {
        $request = new Request("DELETE", $url, $headers);
        return $this->send($request, $options);
    }

    /**
     * Send a PATCH request.
     */
    public function patch(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = []
    ): Result {
        $stream = $this->prepareBody($body, $headers);
        $request = new Request("PATCH", $url, $headers, $stream);
        return $this->send($request, $options);
    }

    /**
     * Send a HEAD request.
     */
    public function head(
        string $url,
        array $headers = [],
        array $options = []
    ): Result {
        $request = new Request("HEAD", $url, $headers);
        return $this->send($request, $options);
    }

    /**
     * Get the default CA bundle path.
     * Tries multiple common locations.
     */
    private static function getDefaultCABundle(): ?string
    {
        // Common CA bundle locations
        $possiblePaths = [
            // Set by environment
            getenv('SSL_CERT_FILE'),
            getenv('CURL_CA_BUNDLE'),

            // Common Linux/Unix paths
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/ca-bundle.pem',
            '/etc/ssl/cert.pem',
            '/usr/local/share/certs/ca-root-nss.crt',
            '/usr/share/ssl/certs/ca-bundle.crt',

            // macOS
            '/usr/local/etc/openssl/cert.pem',
            '/usr/local/etc/openssl@1.1/cert.pem',
            '/usr/local/etc/openssl@3/cert.pem',
            '/opt/homebrew/etc/ca-certificates/cert.pem',

            // Windows (with Git)
            'C:\Program Files\Git\mingw64\ssl\certs\ca-bundle.crt',
            'C:\Program Files (x86)\Git\mingw32\ssl\certs\ca-bundle.crt',

            // PHP configuration
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
        ];

        // Check each possible path
        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path) && is_readable($path)) {
                return $path;
            }
        }

        // Try to download if not found
        return self::downloadCABundle();
    }

    /**
     * Download CA bundle from curl.se
     */
    private static function downloadCABundle(): ?string
    {
        $cacheDir = sys_get_temp_dir() . '/vosaka-http';
        $cacertPath = $cacheDir . '/cacert.pem';

        // Check if already downloaded and fresh (less than 30 days)
        if (file_exists($cacertPath)) {
            $age = time() - filemtime($cacertPath);
            if ($age < 30 * 24 * 60 * 60) { // 30 days
                return $cacertPath;
            }
        }

        // Create directory
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        // Download using system curl or file_get_contents
        $url = 'https://curl.se/ca/cacert.pem';

        // Try curl command first
        if (PHP_OS_FAMILY !== 'Windows') {
            $cmd = sprintf(
                'curl -fsSL -o %s %s',
                escapeshellarg($cacertPath),
                escapeshellarg($url)
            );
            exec($cmd, $output, $result);
            if ($result === 0 && file_exists($cacertPath)) {
                return $cacertPath;
            }
        }

        // Fallback to file_get_contents
        $context = stream_context_create([
            'http' => ['timeout' => 30],
            'ssl' => ['verify_peer' => false] // OK for downloading CA bundle
        ]);

        $content = @file_get_contents($url, false, $context);
        if ($content !== false) {
            file_put_contents($cacertPath, $content);
            return $cacertPath;
        }

        return null;
    }

    /**
     * Configure cURL handle with request options.
     */
    private function configureCurl(
        mixed $ch,
        RequestInterface $request,
        array $options
    ): void {
        $uri = $request->getUri();
        $url = (string)$uri;

        // Basic options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());

        // Timeouts
        curl_setopt($ch, CURLOPT_TIMEOUT, $options["timeout"]);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options["connect_timeout"]);

        // Follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $options["follow_redirects"]);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $options["max_redirects"]);

        // SSL options
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $options["ssl_verify"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $options["ssl_verify"] ? 2 : 0);

        // CA bundle
        if ($options["ssl_ca_bundle"] !== null) {
            curl_setopt($ch, CURLOPT_CAINFO, $options["ssl_ca_bundle"]);
        }

        // Client certificate
        if ($options["ssl_cert"] !== null) {
            curl_setopt($ch, CURLOPT_SSLCERT, $options["ssl_cert"]);
        }

        // Client key
        if ($options["ssl_key"] !== null) {
            curl_setopt($ch, CURLOPT_SSLKEY, $options["ssl_key"]);
        }

        // Headers
        $headers = $this->buildHeaders($request, $options);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Body
        $body = $request->getBody();
        if ($body->getSize() > 0) {
            $body->rewind();
            $bodyContent = $body->getContents();
            curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyContent);
        }

        // Proxy
        if ($options["proxy"] !== null) {
            curl_setopt($ch, CURLOPT_PROXY, $options["proxy"]);
        }

        // Cookies
        if ($options["cookies"] !== null) {
            curl_setopt($ch, CURLOPT_COOKIE, $options["cookies"]);
        }
    }

    /**
     * Execute cURL request asynchronously.
     */
    private function executeAsync(mixed $ch): Generator
    {
        $active = null;

        // Start execution
        do {
            $status = curl_multi_exec(self::$multiHandle, $active);
            yield;
        } while ($status === CURLM_CALL_MULTI_PERFORM);

        if ($status !== CURLM_OK) {
            throw new RuntimeException("cURL multi exec failed: " . curl_multi_strerror($status));
        }

        // Wait for completion
        while ($active && $status === CURLM_OK) {
            yield;

            // Wait for activity
            $select = curl_multi_select(self::$multiHandle, 0.1);

            if ($select === -1) {
                continue;
            }

            // Continue execution
            do {
                $status = curl_multi_exec(self::$multiHandle, $active);
                yield;
            } while ($status === CURLM_CALL_MULTI_PERFORM);
        }

        // Check for completed transfers
        while ($info = curl_multi_info_read(self::$multiHandle)) {
            if ($info['handle'] === $ch) {
                if ($info['result'] !== CURLE_OK) {
                    $error = curl_error($ch);
                    throw new RuntimeException("cURL request failed: " . $error);
                }

                // Get response
                $response = $this->parseResponse($ch);
                return $response;
            }
        }

        throw new RuntimeException("Request did not complete");
    }

    /**
     * Parse cURL response into Response object.
     */
    private function parseResponse(mixed $ch): Response
    {
        $responseData = curl_multi_getcontent($ch);
        if ($responseData === false) {
            throw new RuntimeException("Failed to get cURL response content");
        }

        // Get response info
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        // Split headers and body
        $headerString = substr($responseData, 0, $headerSize);
        $bodyString = substr($responseData, $headerSize);

        // Parse headers
        $headers = [];
        $reasonPhrase = "";
        $protocolVersion = "1.1";

        $headerLines = explode("\r\n", trim($headerString));
        foreach ($headerLines as $i => $line) {
            if ($i === 0) {
                // Status line
                if (preg_match('/^HTTP\/(\d\.\d)\s+(\d{3})\s*(.*)$/', $line, $matches)) {
                    $protocolVersion = $matches[1];
                    $reasonPhrase = $matches[3] ?? "";
                }
            } elseif (!empty($line) && str_contains($line, ":")) {
                // Header line
                [$name, $value] = explode(":", $line, 2);
                $name = trim($name);
                $value = trim($value);

                if (!isset($headers[$name])) {
                    $headers[$name] = [];
                }
                $headers[$name][] = $value;
            }
        }

        // Create response
        $body = Stream::create($bodyString);

        return new Response(
            $httpCode,
            $headers,
            $body,
            $protocolVersion,
            $reasonPhrase
        );
    }

    /**
     * Build headers array for cURL.
     */
    private function buildHeaders(RequestInterface $request, array $options): array
    {
        $headers = [];

        // Add default headers
        $defaultHeaders = array_merge(
            [
                "User-Agent" => $options["user_agent"],
                "Accept" => "*/*",
            ],
            $options["headers"]
        );

        // Merge with request headers
        $allHeaders = array_merge($defaultHeaders, $request->getHeaders());

        // Format for cURL
        foreach ($allHeaders as $name => $values) {
            foreach ((array)$values as $value) {
                $headers[] = "$name: $value";
            }
        }

        return $headers;
    }

    /**
     * Prepare request body.
     */
    private function prepareBody(mixed $body, array &$headers): Stream
    {
        if ($body === null) {
            return Stream::create("");
        }

        if (is_string($body)) {
            return Stream::create($body);
        }

        if (is_array($body)) {
            $json = json_encode($body, JSON_THROW_ON_ERROR);
            if (!isset($headers["Content-Type"])) {
                $headers["Content-Type"] = "application/json";
            }
            return Stream::create($json);
        }

        if ($body instanceof Stream) {
            return $body;
        }

        throw new InvalidArgumentException("Invalid body type");
    }

    /**
     * Validate URI.
     */
    private function validateUri($uri): void
    {
        $scheme = $uri->getScheme();
        if (!in_array($scheme, ["http", "https"], true)) {
            throw new InvalidArgumentException(
                "Only HTTP and HTTPS URLs are supported"
            );
        }

        if ($uri->getHost() === "") {
            throw new InvalidArgumentException("URL must contain a host");
        }
    }

    /**
     * Cleanup mixeds on destruction.
     */
    public function __destruct()
    {
        // Clean up any remaining handles
        foreach (self::$activeHandles as $ch) {
            curl_multi_remove_handle(self::$multiHandle, $ch);
            curl_close($ch);
        }
    }

    /**
     * Close the multi handle (call this when shutting down).
     */
    public static function shutdown(): void
    {
        if (self::$multiHandle !== null) {
            curl_multi_close(self::$multiHandle);
            self::$multiHandle = null;
        }
        self::$activeHandles = [];
    }
}
