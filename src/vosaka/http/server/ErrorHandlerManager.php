<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Closure;
use Throwable;
use vosaka\http\message\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use vosaka\http\exceptions\HttpException;
use vosaka\http\message\Stream;
use vosaka\http\router\Router;

final class ErrorHandlerManager
{
    private ?Closure $errorHandler = null;
    private ?Closure $notFoundHandler = null;
    private ?Closure $methodNotAllowedHandler = null;
    private Router $router;
    private bool $debugMode;

    public function __construct(Router $router, bool $debugMode = false)
    {
        $this->router = $router;
        $this->debugMode = $debugMode;
        $this->errorHandler = $this->defaultErrorHandler(...);
        $this->notFoundHandler = $this->defaultNotFoundHandler(...);
        $this->methodNotAllowedHandler = $this->defaultMethodNotAllowedHandler(
            ...
        );
    }

    public function setErrorHandler(Closure $handler): void
    {
        $this->errorHandler = $handler;
    }

    public function setNotFoundHandler(Closure $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    public function setMethodNotAllowedHandler(Closure $handler): void
    {
        $this->methodNotAllowedHandler = $handler;
    }

    public function handleError(
        Throwable $error,
        ?ServerRequestInterface $request = null
    ): ResponseInterface {
        return ($this->errorHandler)($error, $request);
    }

    public function handleNotFound(
        ServerRequestInterface $request
    ): ResponseInterface {
        return ($this->notFoundHandler)($request);
    }

    public function handleMethodNotAllowed(
        ServerRequestInterface $request,
        array $allowedMethods
    ): ResponseInterface {
        return ($this->methodNotAllowedHandler)($request, $allowedMethods);
    }

    private function defaultNotFoundHandler(
        ServerRequestInterface $request
    ): ResponseInterface {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        if ($this->debugMode) {
            $body = json_encode(
                [
                    "error" => "Not Found",
                    "message" => "No route found for {$method} {$path}",
                    "available_routes" => $this->router->getRoutesByMethod(),
                ],
                JSON_PRETTY_PRINT
            );

            return new Response(
                404,
                ["Content-Type" => "application/json"],
                Stream::create($body)
            );
        }

        return new Response(404, [], Stream::create("Not Found"));
    }

    private function defaultMethodNotAllowedHandler(
        ServerRequestInterface $request,
        array $allowedMethods
    ): ResponseInterface {
        $headers = [
            "Allow" => implode(", ", $allowedMethods),
        ];

        if ($this->debugMode) {
            $body = json_encode(
                [
                    "error" => "Method Not Allowed",
                    "message" => "Method {$request->getMethod()} not allowed for {$request->getUri()->getPath()}",
                    "allowed_methods" => $allowedMethods,
                ],
                JSON_PRETTY_PRINT
            );

            $headers["Content-Type"] = "application/json";
            return new Response(405, $headers, Stream::create($body));
        }

        return new Response(
            405,
            $headers,
            Stream::create("Method Not Allowed")
        );
    }

    private function defaultErrorHandler(
        Throwable $error,
        ?ServerRequestInterface $request = null
    ): ResponseInterface {
        $statusCode =
            $error instanceof HttpException ? $error->getStatusCode() : 500;

        if ($this->debugMode && $statusCode === 500) {
            $body = json_encode(
                [
                    "error" => get_class($error),
                    "message" => $error->getMessage(),
                    "file" => $error->getFile(),
                    "line" => $error->getLine(),
                    "trace" => explode("\n", $error->getTraceAsString()),
                ],
                JSON_PRETTY_PRINT
            );

            return new Response(
                $statusCode,
                ["Content-Type" => "application/json"],
                Stream::create($body)
            );
        }

        $message =
            $statusCode === 500
                ? "Internal Server Error"
                : $error->getMessage();
        return new Response($statusCode, [], Stream::create($message));
    }
}
