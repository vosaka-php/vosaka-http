<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Closure;
use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use vosaka\foroutines\Launch;
use vosaka\foroutines\Pause;
use vosaka\foroutines\RunBlocking;
use vosaka\http\middleware\MiddlewareInterface;
use vosaka\http\middleware\MiddlewareStack;
use vosaka\http\router\Router;

/**
 * HttpServer pipeline:
 * socket -> read -> string buffer -> HTTP parser -> handler
 * -> response builder -> string -> socket write
 */
final class HttpServer
{
    public bool  $running  = false;
    public mixed $listener = null;

    private Router              $router;
    public  MiddlewareStack     $middlewareStack;
    public  ErrorHandlerManager $errorHandlers;
    private ServerConfig        $config;
    public  bool                $debugMode = false;

    private HttpRequestParser  $requestParser;
    private RequestProcessor   $requestProcessor;
    private ServerDebugHelper  $debugHelper;
    private HttpResponseWriter $responseWriter;

    private const INITIAL_READ_BYTES = 65536;
    private const READ_TIMEOUT_S     = 30.0;
    private const MAX_HEADER_BYTES   = 8192;

    public function __construct(Router $router, ?ServerConfig $config = null)
    {
        $this->config           = $config ?? new ServerConfig();
        $this->router           = $router;
        $this->middlewareStack  = new MiddlewareStack();
        $this->errorHandlers    = new ErrorHandlerManager($router, false);
        $this->requestParser    = new HttpRequestParser($this->config);
        $this->responseWriter   = new HttpResponseWriter();
        $this->requestProcessor = new RequestProcessor(
            $this->router,
            $this->middlewareStack,
            $this->errorHandlers,
            false,
        );
        $this->debugHelper = new ServerDebugHelper($this->router);
    }

    public static function new(Router $router, ?ServerConfig $config = null): self
    {
        return new self($router, $config);
    }

    public function bind(string $address): ServerBuilder
    {
        return new ServerBuilder($this, $address);
    }

    public function withDebugMode(bool $debug = true): self
    {
        $this->debugMode        = $debug;
        $this->errorHandlers    = new ErrorHandlerManager($this->router, $debug);
        $this->requestProcessor = new RequestProcessor(
            $this->router,
            $this->middlewareStack,
            $this->errorHandlers,
            $debug,
        );
        return $this;
    }

    public function withErrorHandler(Closure $handler): self
    {
        $this->errorHandlers->setErrorHandler($handler);
        return $this;
    }

    public function withNotFoundHandler(Closure $handler): self
    {
        $this->errorHandlers->setNotFoundHandler($handler);
        return $this;
    }

    public function withMethodNotAllowedHandler(Closure $handler): self
    {
        $this->errorHandlers->setMethodNotAllowedHandler($handler);
        return $this;
    }

    public function layer(MiddlewareInterface|Closure $middleware): self
    {
        $this->middlewareStack->push($middleware);
        return $this;
    }

    public function serve(string $address, array $options = []): void
    {
        $this->listener = $this->createServerSocket($address, $options);
        $this->running  = true;

        Pause::setBatchSize(0);

        if ($this->debugMode) {
            $this->debugHelper->printRouteTable();
            echo "Listening on tcp://{$address}\n";
        }

        RunBlocking::new(function (): void {
            $this->acceptLoop();
        });
    }

    public function shutdown(): void
    {
        $this->running = false;
        if (is_resource($this->listener)) {
            fclose($this->listener);
            $this->listener = null;
        }
    }

    public function acceptLoop(): void
    {
        while ($this->running) {
            if (!is_resource($this->listener)) {
                break;
            }

            $read   = [$this->listener];
            $write  = null;
            $except = null;
            $ready  = @stream_select($read, $write, $except, 0, 0);

            if ($ready === false) {
                break;
            }

            if ($ready > 0) {
                while (true) {
                    $client = @stream_socket_accept($this->listener, 0);
                    if ($client === false) {
                        break;
                    }
                    stream_set_blocking($client, false);
                    Launch::new(function () use ($client): void {
                        $this->handleConnection($client);
                    });
                }
            }

            Pause::new();
        }
    }

    private function handleConnection(mixed $socket): void
    {
        try {
            $keepAlive = true;

            while ($keepAlive && is_resource($socket)) {
                $rawRequest = $this->readRawRequest($socket);
                if ($rawRequest === '') {
                    break;
                }

                $request = $this->parseBufferedRequest($rawRequest);
                if ($request === null) {
                    break;
                }

                $response    = $this->requestProcessor->processRequest($request);
                $rawResponse = $this->buildRawResponse($response);
                $this->writeRawResponse($socket, $rawResponse);
                $keepAlive = $this->responseWriter->shouldKeepAlive($request, $response);

                // FIX 1: Only yield when keep-alive is active and we expect more
                // requests on this connection. Yielding unconditionally adds one
                // scheduler round-trip per request even on short-lived connections.
                if ($keepAlive) {
                    Pause::force();
                }
            }
        } catch (Throwable $e) {
            try {
                $errorResponse = $this->errorHandlers->handleError($e);
                $rawResponse   = $this->buildRawResponse($errorResponse);
                $this->writeRawResponse($socket, $rawResponse);
            } catch (Throwable) {
            }
        } finally {
            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    private function readRawRequest(mixed $socket): string
    {
        $deadline  = microtime(true) + self::READ_TIMEOUT_S;
        $buffer    = '';
        $targetLen = null;

        while (true) {
            $chunk = @fread($socket, self::INITIAL_READ_BYTES);

            if ($chunk === false) {
                break;
            }

            if ($chunk !== '') {
                $buffer .= $chunk;

                if (strlen($buffer) > self::MAX_HEADER_BYTES && $targetLen === null) {
                    break;
                }

                if ($targetLen === null) {
                    $headerEnd = strpos($buffer, "\r\n\r\n");
                    if ($headerEnd !== false) {
                        $headersEndPos = $headerEnd + 4;
                        $contentLength = 0;
                        $headers       = substr($buffer, 0, $headersEndPos);
                        if (preg_match('/\r\nContent-Length:\s*(\d+)/i', $headers, $m)) {
                            $contentLength = (int) $m[1];
                        }
                        $targetLen = $headersEndPos + $contentLength;
                    }
                }

                if ($targetLen !== null && strlen($buffer) >= $targetLen) {
                    break;
                }
            }

            if (feof($socket)) {
                break;
            }

            if (microtime(true) >= $deadline) {
                break;
            }

            Pause::force();
        }

        return $buffer;
    }

    private function parseBufferedRequest(string $raw): ?ServerRequestInterface
    {
        $stream = fopen('php://memory', 'r+b');
        if ($stream === false) {
            return null;
        }

        fwrite($stream, $raw);
        rewind($stream);
        stream_set_blocking($stream, false);

        try {
            return $this->requestParser->parseRequest($stream);
        } finally {
            fclose($stream);
        }
    }

    private function buildRawResponse(ResponseInterface $response): string
    {
        $body = $response->getBody();
        $body->rewind();
        $bodyString = $body->getContents();

        // Inject missing default headers before iterating once over the header map.
        if (!$response->hasHeader('Content-Length')) {
            $response = $response->withHeader('Content-Length', (string) strlen($bodyString));
        }
        if (!$response->hasHeader('Server')) {
            $response = $response->withHeader('Server', 'VOsaka-HTTP/2.0');
        }
        if (!$response->hasHeader('Date')) {
            $response = $response->withHeader('Date', gmdate('D, d M Y H:i:s') . ' GMT');
        }

        // FIX 2: Collect header lines into an array and join once instead of
        // repeatedly appending to a string (avoids O(n²) copies for many headers).
        $headerLines = [
            sprintf(
                "HTTP/%s %d %s",
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ),
        ];

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headerLines[] = "{$name}: {$value}";
            }
        }

        // Two CRLF: one ends the last header, one is the blank line before body.
        return implode("\r\n", $headerLines) . "\r\n\r\n" . $bodyString;
    }

    private function writeRawResponse(mixed $socket, string $raw): void
    {
        $written = 0;
        $len     = strlen($raw);

        // FIX 3: Avoid allocating a new substring on every partial write.
        // stream_set_chunk_size + a single fwrite handles the common case (full
        // write in one syscall). The loop fallback only triggers under back-pressure.
        while ($written < $len && is_resource($socket)) {
            $bytes = @fwrite($socket, $raw, $len);

            if ($bytes === false || $bytes === 0) {
                break;
            }

            $written += $bytes;

            if ($written < $len) {
                // Partial write — trim the already-sent prefix once and continue.
                $raw = substr($raw, $bytes);
                $len -= $bytes;
            }
        }
    }

    /** @return resource */
    public function createServerSocket(string $address, array $options): mixed
    {
        $contextOptions = array_merge_recursive(
            ['socket' => ['so_reuseport' => true, 'so_reuseaddr' => true, 'backlog' => 65535]],
            $options,
        );
        $context = stream_context_create($contextOptions);

        $socket = @stream_socket_server(
            "tcp://{$address}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context,
        );

        if ($socket === false) {
            throw new \RuntimeException("Failed to bind tcp://{$address}: [{$errno}] {$errstr}");
        }

        stream_set_blocking($socket, false);
        return $socket;
    }
}
