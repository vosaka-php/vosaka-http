<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use venndev\vosaka\net\tcp\TCPConnection;
use vosaka\http\exceptions\HttpException;
use vosaka\http\message\ServerRequest;
use vosaka\http\message\Stream;
use vosaka\http\message\Uri;

final class HttpRequestParser
{
    private ServerConfig $config;

    public function __construct(ServerConfig $config)
    {
        $this->config = $config;
    }

    public function parseRequest(TCPConnection $client): Generator
    {
        $requestLine = yield from $client->readUntil("\r\n")->unwrap();
        if (empty($requestLine)) {
            return null;
        }

        $parsedRequestLine = $this->parseRequestLine($requestLine);
        if (!$parsedRequestLine) {
            throw new HttpException("Invalid HTTP request line", 400);
        }

        [$method, $target, $version] = $parsedRequestLine;
        $headers = yield from $this->parseHeaders($client);
        $body = yield from $this->parseBody($client, $headers);

        $uri = $this->parseUri($target);
        $serverParams = $this->buildServerParams(
            $method,
            $target,
            $version,
            $client
        );

        $request = new ServerRequest(
            $method,
            $uri,
            $headers,
            $body,
            $version,
            $serverParams
        );

        return $this->enrichRequest($request, $uri, $body->getContents());
    }

    private function parseRequestLine(string $line): ?array
    {
        // Fast path for common case - avoid regex
        $parts = explode(" ", $line, 3);
        if (count($parts) !== 3) {
            return null;
        }

        [$method, $uri, $version] = $parts;

        // Quick validation
        if (!ctype_alpha($method) || !str_starts_with($version, "HTTP/")) {
            return null;
        }

        return [$method, $uri, substr($version, 5)]; // Remove 'HTTP/' prefix
    }

    private function parseHeaders(TCPConnection $client): Generator
    {
        $headers = [];
        while (true) {
            $line = yield from $client->readUntil("\r\n")->unwrap();
            if (empty($line)) {
                break;
            }

            $colonPos = strpos($line, ":");
            if ($colonPos !== false) {
                $name = strtolower(trim(substr($line, 0, $colonPos)));
                $value = trim(substr($line, $colonPos + 1));
                $headers[$name][] = $value;
            }
        }
        return $headers;
    }

    private function parseBody(TCPConnection $client, array $headers): Generator
    {
        $contentLength = (int) ($headers["content-length"][0] ?? 0);

        if ($contentLength > $this->config->maxRequestSize) {
            throw new HttpException("Request body too large", 413);
        }

        // Fast path for no body
        if ($contentLength === 0) {
            return Stream::create("");
        }

        $bodyContent = yield from $client->read($contentLength)->unwrap();
        return Stream::create($bodyContent);
    }

    private function buildServerParams(
        string $method,
        string $target,
        string $version
    ): array {
        static $time = null;
        static $timeFloat = null;
        static $lastUpdate = 0;

        // Update time every second only
        $now = time();
        if ($time === null || $now > $lastUpdate) {
            $time = $now;
            $timeFloat = microtime(true);
            $lastUpdate = $now;
        }

        return [
            "REQUEST_METHOD" => $method,
            "REQUEST_URI" => $target,
            "SERVER_PROTOCOL" => "HTTP/$version",
            "REQUEST_TIME" => $time,
            "REQUEST_TIME_FLOAT" => $timeFloat,
        ];
    }

    private function parseUri(string $target): Uri
    {
        // Fast path for relative URIs (most common case)
        if ($target[0] === "/") {
            return new Uri("http://localhost$target");
        }
        return new Uri($target);
    }

    private function enrichRequest(
        ServerRequestInterface $request,
        Uri $uri,
        string $body
    ): ServerRequestInterface {
        $query = $uri->getQuery();
        if ($query !== "") {
            $queryParams = [];
            parse_str($query, $queryParams);
            $request = $request->withQueryParams($queryParams);
        }

        // Fast path - skip body parsing for GET/HEAD/DELETE
        $method = $request->getMethod();
        if ($method === "GET" || $method === "HEAD" || $method === "DELETE") {
            return $request;
        }

        // Only parse body for POST/PUT/PATCH requests with form data
        if ($body !== "" && $this->isFormData($request)) {
            $parsedBody = [];
            parse_str($body, $parsedBody);
            $request = $request->withParsedBody($parsedBody);
        }

        return $request;
    }

    private function isFormData(ServerRequestInterface $request): bool
    {
        $contentType = $request->getHeaderLine("Content-Type");
        return str_contains($contentType, "application/x-www-form-urlencoded");
    }
}
