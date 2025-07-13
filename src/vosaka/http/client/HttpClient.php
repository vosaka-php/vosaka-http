<?php

declare(strict_types=1);

namespace vosaka\http\client;

use Generator;
use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;
use venndev\vosaka\net\tcp\TCP;
use venndev\vosaka\net\tcp\TCPStream;
use vosaka\http\message\Request;
use vosaka\http\message\Response;
use vosaka\http\message\Stream;
use vosaka\http\message\Uri;

/**
 * Asynchronous HTTP Client using VOsaka runtime.
 *
 * This client provides async HTTP request capabilities using the VOsaka
 * event loop system. It supports GET, POST, PUT, DELETE and other HTTP
 * methods with configurable timeouts, headers, and SSL options.
 */
final class HttpClient
{
    private array $defaultOptions = [
        "timeout" => 30,
        "follow_redirects" => true,
        "max_redirects" => 1,
        "ssl_verify" => true,
        "user_agent" => "VOsaka-HTTP/1.0",
        "headers" => [],
    ];

    public function __construct(array $options = [])
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);
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

            $redirectCount = 0;
            $currentRequest = $request;

            while ($redirectCount <= $options["max_redirects"]) {
                $response = yield from $this->sendSingleRequest(
                    $currentRequest,
                    $options
                );

                if (
                    $options["follow_redirects"] &&
                    $this->isRedirectStatus($response->getStatusCode())
                ) {
                    $location = $response->getHeaderLine("Location");
                    if ($location === "") {
                        break;
                    }

                    $redirectCount++;
                    if ($redirectCount > $options["max_redirects"]) {
                        throw new RuntimeException(
                            "Maximum redirect limit exceeded"
                        );
                    }

                    $newUri = Uri::resolve(
                        $currentRequest->getUri(),
                        new Uri($location)
                    );
                    $currentRequest = $currentRequest->withUri($newUri);

                    if ($response->getStatusCode() === 303) {
                        $currentRequest = $currentRequest
                            ->withMethod("GET")
                            ->withBody(Stream::create(""));
                    }

                    continue;
                }

                return $response;
            }

            return $response ??
                throw new RuntimeException("No response received");
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
     * Send a OPTIONS request.
     */
    public function options(
        string $url,
        array $headers = [],
        array $options = []
    ): Result {
        $request = new Request("OPTIONS", $url, $headers);
        return $this->send($request, $options);
    }

    private function sendSingleRequest(
        RequestInterface $request,
        array $options
    ): Generator {
        $uri = $request->getUri();
        $host = $uri->getHost();
        $port = $uri->getPort() ?? ($uri->getScheme() === "https" ? 443 : 80);
        $scheme = $uri->getScheme();

        // Connect to server
        $connectionOptions = [
            "ssl" => $scheme === "https",
            "timeout" => $options["timeout"],
        ];

        $stream = yield from TCP::connect(
            "$host:$port",
            $connectionOptions
        )->unwrap();

        try {
            // Send request
            $requestData = $this->buildHttpRequest($request, $options);
            $writeResult = yield from $stream->write($requestData)->unwrap();

            // Read response
            $response = yield from $this->readHttpResponse($stream);
            return $response;
        } finally {
            $stream->close();
        }
    }

    private function buildHttpRequest(
        RequestInterface $request,
        array $options
    ): string {
        $uri = $request->getUri();
        $method = $request->getMethod();
        $target = $request->getRequestTarget();

        // Start with request line
        $http = "$method $target HTTP/1.1\r\n";

        // Add headers
        $headers = array_merge(
            $this->getDefaultHeaders($options),
            $request->getHeaders()
        );

        // Ensure Host header is set
        if (!$request->hasHeader("Host")) {
            $host = $uri->getHost();
            if ($uri->getPort() !== null) {
                $host .= ":" . $uri->getPort();
            }
            $headers["Host"] = [$host];
        }

        // Add content-length for body
        $body = $request->getBody();
        if ($body->getSize() > 0) {
            $headers["Content-Length"] = [$body->getSize()];
        }

        foreach ($headers as $name => $values) {
            foreach ((array) $values as $value) {
                $http .= "$name: $value\r\n";
            }
        }

        $http .= "\r\n";

        // Add body
        if ($body->getSize() > 0) {
            $body->rewind();
            $http .= $body->getContents();
        }

        return $http;
    }

    private function readHttpResponse(TCPStream $stream): Generator
    {
        // Read status line
        $statusLine = yield from $stream->readUntil("\r\n")->unwrap();

        if ($statusLine === null) {
            throw new RuntimeException("Failed to read response status line");
        }

        if (
            !preg_match(
                '/^HTTP\/(\d\.\d)\s+(\d{3})\s*(.*)$/',
                $statusLine,
                $matches
            )
        ) {
            throw new RuntimeException("Invalid HTTP response status line");
        }

        $protocolVersion = $matches[1];
        $statusCode = (int) $matches[2];
        $reasonPhrase = $matches[3] ?? "";

        // Read headers
        $headers = [];
        while (true) {
            $line = yield from $stream->readUntil("\r\n")->unwrap();

            if ($line === null || $line === "") {
                break;
            }

            if (!str_contains($line, ":")) {
                continue;
            }

            [$name, $value] = explode(":", $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!isset($headers[$name])) {
                $headers[$name] = [];
            }
            $headers[$name][] = $value;
        }

        // Read body
        $bodyContent = "";
        $contentLength = null;
        $transferEncoding = null;

        foreach ($headers as $name => $values) {
            $lowerName = strtolower($name);
            if ($lowerName === "content-length") {
                $contentLength = (int) $values[0];
            } elseif ($lowerName === "transfer-encoding") {
                $transferEncoding = strtolower($values[0]);
            }
        }

        if ($transferEncoding === "chunked") {
            $bodyContent = yield from $this->readChunkedBody($stream);
        } elseif ($contentLength !== null && $contentLength > 0) {
            $bodyContent = yield from $stream
                ->readExact($contentLength)
                ->unwrap();
        }

        $body = Stream::create($bodyContent);

        return new Response(
            $statusCode,
            $headers,
            $body,
            $protocolVersion,
            $reasonPhrase
        );
    }

    private function readChunkedBody(TCPStream $stream): Generator
    {
        $body = "";

        while (true) {
            // Read chunk size line
            $chunkSizeLine = yield from $stream->readUntil("\r\n")->unwrap();

            if ($chunkSizeLine === null) {
                break;
            }

            $chunkSize = hexdec(explode(";", $chunkSizeLine)[0]);

            // Read chunk data
            $chunkData = yield from $stream->readExact($chunkSize)->unwrap();
            $body .= $chunkData;
        }

        return $body;
    }

    private function getDefaultHeaders(array $options): array
    {
        return array_merge(
            [
                "User-Agent" => [$options["user_agent"]],
                "Connection" => ["close"],
                "Accept" => ["*/*"],
            ],
            $options["headers"]
        );
    }

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

    private function isRedirectStatus(int $statusCode): bool
    {
        return in_array($statusCode, [301, 302, 303, 307, 308], true);
    }
}
