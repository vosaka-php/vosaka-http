<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use vosaka\foroutines\AsyncIO;

/**
 * HttpResponseWriter — vosaka-foroutines (Fiber + AsyncIO).
 *
 * Replaces the old Generator / TCPConnection::write()->unwrap() approach.
 * AsyncIO::streamWrite($socket, $data)->await() suspends the current Fiber
 * via waitForWrite() + Fiber::suspend() while the kernel drains the send
 * buffer — zero blocking, other fibers keep running.
 *
 * Caller must be inside a Fiber (Launch::new / Async::new).
 *
 *   $writer = new HttpResponseWriter();
 *   $writer->sendResponse($socket, $response);   // suspends fiber during I/O
 */
final class HttpResponseWriter
{
    // Pre-built status lines for the most common codes — avoids sprintf() on hot path
    private const STATUS_LINES = [
        200 => "HTTP/1.1 200 OK\r\n",
        201 => "HTTP/1.1 201 Created\r\n",
        204 => "HTTP/1.1 204 No Content\r\n",
        301 => "HTTP/1.1 301 Moved Permanently\r\n",
        302 => "HTTP/1.1 302 Found\r\n",
        304 => "HTTP/1.1 304 Not Modified\r\n",
        400 => "HTTP/1.1 400 Bad Request\r\n",
        401 => "HTTP/1.1 401 Unauthorized\r\n",
        403 => "HTTP/1.1 403 Forbidden\r\n",
        404 => "HTTP/1.1 404 Not Found\r\n",
        405 => "HTTP/1.1 405 Method Not Allowed\r\n",
        409 => "HTTP/1.1 409 Conflict\r\n",
        413 => "HTTP/1.1 413 Content Too Large\r\n",
        422 => "HTTP/1.1 422 Unprocessable Entity\r\n",
        429 => "HTTP/1.1 429 Too Many Requests\r\n",
        431 => "HTTP/1.1 431 Request Header Fields Too Large\r\n",
        500 => "HTTP/1.1 500 Internal Server Error\r\n",
        502 => "HTTP/1.1 502 Bad Gateway\r\n",
        503 => "HTTP/1.1 503 Service Unavailable\r\n",
    ];

    // ── Public API ───────────────────────────────────────────────────────────

    /**
     * Serialize a PSR-7 response and write it to the socket.
     *
     * Suspends the Fiber during the write so other requests can be handled
     * concurrently. No return value — throws on I/O error.
     *
     * @param resource          $socket   Non-blocking client socket.
     * @param ResponseInterface $response PSR-7 response to send.
     */
    public function sendResponse(mixed $socket, ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $statusLine = self::STATUS_LINES[$statusCode]
            ?? sprintf(
                "HTTP/%s %d %s\r\n",
                $response->getProtocolVersion(),
                $statusCode,
                $response->getReasonPhrase(),
            );

        $body       = $this->readBody($response);
        $bodyLength = strlen($body);

        // Ensure Content-Length is set — required for keep-alive correctness
        if (!$response->hasHeader('Content-Length')) {
            $response = $response->withHeader('Content-Length', (string) $bodyLength);
        }

        $headers = $this->buildHeaderBlock($response);

        // Assemble the full HTTP message in one string to minimize write() calls.
        // For large bodies (> ~1 MB) a two-write approach would be better,
        // but for typical API responses a single write is faster.
        $raw = $statusLine . $headers . "\r\n" . $body;

        try {
            AsyncIO::streamWrite($socket, $raw)->await();
        } catch (\Throwable) {
            // Client disconnected mid-write (EPIPE / broken pipe) — normal
            // under high load. handleConnection's finally block closes socket.
        }
    }

    /**
     * Determine whether the connection should be kept alive after this
     * response, based on the request's Connection header and the response.
     *
     * HTTP/1.1 default: keep-alive.
     * HTTP/1.0 default: close.
     */
    public function shouldKeepAlive(
        ServerRequestInterface $request,
        ResponseInterface      $response,
    ): bool {
        $responseConn = strtolower($response->getHeaderLine('Connection'));
        if ($responseConn === 'close') {
            return false;
        }
        if ($responseConn === 'keep-alive') {
            return true;
        }

        $requestConn = strtolower($request->getHeaderLine('Connection'));
        if ($requestConn === 'close') {
            return false;
        }

        return $request->getProtocolVersion() === '1.1';
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Build the header block as a single string ending with "\r\n".
     * Injects Server and Date headers if the response does not include them.
     */
    private function buildHeaderBlock(ResponseInterface $response): string
    {
        // Date header is safe to cache for 1 second (RFC 7231 §7.1.1.2)
        static $dateCache    = null;
        static $dateCacheTime = 0;

        $now = time();
        if ($dateCache === null || $now > $dateCacheTime) {
            $dateCache     = 'Date: ' . gmdate('D, d M Y H:i:s') . " GMT\r\n";
            $dateCacheTime = $now;
        }

        $lines      = [];
        $hasServer  = false;
        $hasDate    = false;

        foreach ($response->getHeaders() as $name => $values) {
            $lower = strtolower((string) $name);

            if ($lower === 'server') {
                $hasServer = true;
            }
            if ($lower === 'date') {
                $hasDate = true;
            }

            foreach ($values as $value) {
                $lines[] = "{$name}: {$value}\r\n";
            }
        }

        if (!$hasServer) {
            $lines[] = "Server: VOsaka-HTTP/2.0\r\n";
        }
        if (!$hasDate) {
            $lines[] = $dateCache;
        }

        return implode('', $lines);
    }

    /**
     * Extract the response body as a plain string.
     * Returns empty string for 204 / HEAD-style responses.
     */
    private function readBody(ResponseInterface $response): string
    {
        $body = $response->getBody();
        $size = $body->getSize();

        if ($size === 0) {
            return '';
        }

        $body->rewind();
        return $body->getContents();
    }
}