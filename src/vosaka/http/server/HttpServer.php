<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Throwable;
use Generator;
use Closure;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;
use venndev\vosaka\net\tcp\TCPListener;
use venndev\vosaka\net\tcp\TCPStream;
use vosaka\http\middleware\MiddlewareInterface;
use vosaka\http\middleware\MiddlewareStack;
use vosaka\http\router\Router;

final class HttpServer
{
    private ?TCPListener $listener = null;
    private bool $running = false;
    private Router $router;
    private MiddlewareStack $middlewareStack;
    private ErrorHandlerManager $errorHandlers;
    private ServerConfig $config;
    private bool $debugMode = false;

    private HttpRequestParser $requestParser;
    private HttpResponseWriter $responseWriter;
    private RequestProcessor $requestProcessor;
    private ServerDebugHelper $debugHelper;

    public function __construct(Router $router, ?ServerConfig $config = null)
    {
        $this->config = $config ?? new ServerConfig();
        $this->router = $router;
        $this->middlewareStack = new MiddlewareStack();
        $this->errorHandlers = new ErrorHandlerManager(
            $router,
            $this->debugMode
        );

        // Initialize helper classes
        $this->requestParser = new HttpRequestParser($this->config);
        $this->responseWriter = new HttpResponseWriter();
        $this->requestProcessor = new RequestProcessor(
            $this->router,
            $this->middlewareStack,
            $this->errorHandlers,
            $this->debugMode
        );
        $this->debugHelper = new ServerDebugHelper($this->router);
    }

    public static function new(
        Router $router,
        ?ServerConfig $config = null
    ): self {
        return new self($router, $config);
    }

    public function bind(string $address): ServerBuilder
    {
        return new ServerBuilder($this, $address);
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

    public function withDebugMode(bool $debug = true): self
    {
        $this->debugMode = $debug;
        $this->errorHandlers = new ErrorHandlerManager($this->router, $debug);
        $this->requestProcessor = new RequestProcessor(
            $this->router,
            $this->middlewareStack,
            $this->errorHandlers,
            $debug
        );
        return $this;
    }

    public function layer(MiddlewareInterface|Closure $middleware): self
    {
        $this->middlewareStack->push($middleware);
        return $this;
    }

    public function serve(string $address, array $options = []): Result
    {
        $fn = function () use ($address, $options): Generator {
            $this->listener = yield from TCPListener::bind(
                $address,
                $options
            )->unwrap();
            $this->running = true;

            if ($this->debugMode) {
                $this->debugHelper->printRouteTable();
            }

            while ($this->running) {
                try {
                    $client = yield from $this->listener->accept()->unwrap();
                    if ($client !== null && !$client->isClosed()) {
                        VOsaka::spawn($this->handleConnection($client));
                    }
                    yield;
                } catch (Throwable $e) {
                    if ($this->running) {
                        echo "Error accepting connection: {$e->getMessage()}\n";
                    }
                    break;
                }
            }
        };

        return Result::c($fn());
    }

    public function shutdown(): void
    {
        $this->running = false;
        $this->listener?->close();
    }

    private function handleConnection(TCPStream $client): Generator
    {
        try {
            $keepAlive = true;
            while ($keepAlive && !$client->isClosed()) {
                $request = yield from $this->requestParser->parseRequest(
                    $client
                );
                if ($request === null) {
                    break;
                }

                $response = $this->requestProcessor->processRequest($request);
                yield from $this->responseWriter->sendResponse(
                    $client,
                    $response
                );

                $keepAlive = $this->responseWriter->shouldKeepAlive($request, $response);
            }
        } catch (Throwable $e) {
            try {
                $errorResponse = $this->errorHandlers->handleError($e);
                yield from $this->responseWriter->sendResponse(
                    $client,
                    $errorResponse
                );
            } catch (Throwable) {
                // Silent error handling failure
            }
        } finally {
            $client->close();
        }
    }
}
