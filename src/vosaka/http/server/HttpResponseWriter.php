<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use venndev\vosaka\net\tcp\TCPStream;

final class HttpResponseWriter
{
    public function sendResponse(
        TCPStream $client,
        ResponseInterface $response
    ): Generator {
        // Pre-build common status lines for performance
        static $statusLines = [
            200 => "HTTP/1.1 200 OK\r\n",
            201 => "HTTP/1.1 201 Created\r\n",
            204 => "HTTP/1.1 204 No Content\r\n",
            400 => "HTTP/1.1 400 Bad Request\r\n",
            401 => "HTTP/1.1 401 Unauthorized\r\n",
            403 => "HTTP/1.1 403 Forbidden\r\n",
            404 => "HTTP/1.1 404 Not Found\r\n",
            405 => "HTTP/1.1 405 Method Not Allowed\r\n",
            500 => "HTTP/1.1 500 Internal Server Error\r\n",
        ];

        $statusCode = $response->getStatusCode();
        $statusLine =
            $statusLines[$statusCode] ??
            sprintf(
                "HTTP/%s %d %s\r\n",
                $response->getProtocolVersion(),
                $statusCode,
                $response->getReasonPhrase()
            );

        $headers = $this->buildResponseHeaders($response);
        $body = $this->getResponseBody($response);

        // Build complete response in one go
        $httpResponse = $statusLine . $headers . "\r\n" . $body;
        yield from $client->write($httpResponse)->unwrap();
    }

    private function buildResponseHeaders(ResponseInterface $response): string
    {
        static $serverHeader = "Server: VOsaka-HTTP/2.0";
        static $dateCache = null;
        static $dateCacheTime = 0;

        // Cache date header for 1 second (HTTP standard allows this)
        $now = time();
        if ($dateCache === null || $now > $dateCacheTime) {
            $dateCache = "Date: " . gmdate("D, d M Y H:i:s T");
            $dateCacheTime = $now;
        }

        $headerLines = [];
        $hasServer = false;
        $hasDate = false;
        $hasContentLength = false;

        foreach ($response->getHeaders() as $name => $values) {
            $lowerName = strtolower((string) $name);
            if ($lowerName === "server") {
                $hasServer = true;
            }
            if ($lowerName === "date") {
                $hasDate = true;
            }
            if ($lowerName === "content-length") {
                $hasContentLength = true;
            }

            foreach ($values as $value) {
                $headerLines[] = "$name: $value";
            }
        }

        $body = $response->getBody();
        if (!$hasContentLength && $body->getSize() !== null) {
            $headerLines[] = "Content-Length: " . $body->getSize();
        }

        if (!$hasServer) {
            $headerLines[] = $serverHeader;
        }

        if (!$hasDate) {
            $headerLines[] = $dateCache;
        }

        return implode("\r\n", $headerLines) . "\r\n";
    }

    private function getResponseBody(ResponseInterface $response): string
    {
        $body = $response->getBody();
        $size = $body->getSize();

        if ($size === null || $size === 0) {
            return "";
        }

        $body->rewind();
        return $body->getContents();
    }

    public function shouldKeepAlive(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): bool {
        $connection = strtolower($request->getHeaderLine("Connection"));
        return (!in_array($connection, ["close", ""]) &&
            !$response->hasHeader("Connection")) ||
            strtolower($response->getHeaderLine("Connection")) !== "close";
    }
}
