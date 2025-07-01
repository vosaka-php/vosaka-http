<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Throwable;
use Generator;
use Closure;
use venndev\vosaka\VOsaka;
use venndev\vosaka\net\tcp\TCPListener;
use venndev\vosaka\net\tcp\TCPStream;
use vosaka\http\message\ServerRequest;
use vosaka\http\message\Response;
use vosaka\http\message\Stream;
use vosaka\http\message\Uri;
use venndev\vosaka\core\Result;
use vosaka\http\exceptions\HttpException;
use vosaka\http\middleware\MiddlewareInterface;
use vosaka\http\middleware\MiddlewareStack;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpServer
{
    private ?TCPListener $listener = null;
    private bool $running = false;
    private Router $router;
    private MiddlewareStack $middlewareStack;
    private ?Closure $errorHandler = null;
    private ?Closure $notFoundHandler = null;
    private ?Closure $methodNotAllowedHandler = null;
    private ServerConfig $config;
    private bool $debugMode = false;

    public function __construct(Router $router, ?ServerConfig $config = null)
    {
        $this->config = $config ?? new ServerConfig();
        $this->router = $router;
        $this->middlewareStack = new MiddlewareStack();
        $this->errorHandler = $this->defaultErrorHandler(...);
        $this->notFoundHandler = $this->defaultNotFoundHandler(...);
        $this->methodNotAllowedHandler = $this->defaultMethodNotAllowedHandler(
            ...
        );
    }

    public static function new(
        Router $router,
        ?ServerConfig $config = null
    ): self {
        return new self($router, $config);
    }

    /**
     * Create a server builder for fluent API
     */
    public function bind(string $address): ServerBuilder
    {
        return new ServerBuilder($this, $address);
    }

    public function withErrorHandler(Closure $handler): self
    {
        $this->errorHandler = $handler;
        return $this;
    }

    public function withNotFoundHandler(Closure $handler): self
    {
        $this->notFoundHandler = $handler;
        return $this;
    }

    public function withMethodNotAllowedHandler(Closure $handler): self
    {
        $this->methodNotAllowedHandler = $handler;
        return $this;
    }

    public function withDebugMode(bool $debug = true): self
    {
        $this->debugMode = $debug;
        return $this;
    }

    /**
     * Add server-level middleware (runs before router)
     */
    public function layer(MiddlewareInterface|Closure $middleware): self
    {
        $this->middlewareStack->push($middleware);
        return $this;
    }

    /**
     * Start the server on the given address
     */
    public function serve(string $address, array $options = []): Result
    {
        $fn = function () use ($address, $options): Generator {
            $this->listener = yield from TCPListener::bind(
                $address,
                $options
            )->unwrap();
            $this->running = true;

            if ($this->debugMode) {
                $this->printRouteTable();
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
                        echo "❌ Error accepting connection: {$e->getMessage()}\n";
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
            while (!$client->isClosed()) {
                $request = yield from $this->parseRequest($client);
                if ($request === null) {
                    break;
                }

                $response = yield from $this->processRequest($request);
                yield from $this->sendResponse($client, $response);

                if (!$this->shouldKeepAlive($request, $response)) {
                    break;
                }
            }
        } catch (Throwable $e) {
            try {
                $errorResponse = ($this->errorHandler)($e);
                yield from $this->sendResponse($client, $errorResponse);
            } catch (Throwable) {
                // Silent error handling failure
            }
        } finally {
            $client->close();
        }
    }

    /**
     * Enhanced request processing with proper Router integration
     */
    private function processRequest(ServerRequestInterface $request): Generator
    {
        try {
            // First check if we have a matching route
            $match = $this->router->findMatch($request);

            if ($match === null) {
                // Check if path exists but method is wrong
                $allowedMethods = $this->findAllowedMethods($request);
                if (!empty($allowedMethods)) {
                    return ($this->methodNotAllowedHandler)(
                        $request,
                        $allowedMethods
                    );
                }

                // Truly not found
                return ($this->notFoundHandler)($request);
            }

            // Debug logging
            if ($this->debugMode) {
                $this->logRouteMatch($request, $match);
            }

            // Add route info to request attributes
            $request = $this->enrichRequestWithRouteData($request, $match);

            // Build complete middleware chain: Server middleware + Router handling
            $handler = $this->middlewareStack->build(
                fn(ServerRequestInterface $req) => $this->router->handle($req)
            );

            $response = $handler($request);

            if ($response instanceof Generator) {
                $response = yield from $response;
            }

            return $response instanceof ResponseInterface
                ? $response
                : $this->convertToResponse($response);
        } catch (HttpException $e) {
            return ($this->errorHandler)($e, $request);
        } catch (Throwable $e) {
            if ($this->debugMode) {
                echo "❌ Unhandled error: {$e->getMessage()}\n";
                echo "   File: {$e->getFile()}:{$e->getLine()}\n";
            }
            return ($this->errorHandler)($e, $request);
        }
    }

    /**
     * Find allowed methods for a path (for 405 responses)
     */
    private function findAllowedMethods(ServerRequestInterface $request): array
    {
        $path = rtrim($request->getUri()->getPath(), "/") ?: "/";
        $allowedMethods = [];

        foreach ($this->router->getRoutes() as $route) {
            // Create temporary request with different method to test matching
            $testRequest = $request->withMethod($route->method);
            if ($this->router->findMatch($testRequest) !== null) {
                $allowedMethods[] = $route->method;
            }
        }

        return array_unique($allowedMethods);
    }

    /**
     * Enrich request with route matching data
     */
    private function enrichRequestWithRouteData(
        ServerRequestInterface $request,
        RouteMatch $match
    ): ServerRequestInterface {
        // Add route parameters as attributes
        foreach ($match->params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        // Add route metadata
        $request = $request->withAttribute("_route", $match->route);
        $request = $request->withAttribute("_route_params", $match->params);
        $request = $request->withAttribute("_route_name", $match->route->name);

        return $request;
    }

    /**
     * Debug: Print route table on startup
     */
    private function printRouteTable(): void
    {
        echo "\nRegistered Routes:\n";
        echo str_repeat("-", 60) . "\n";

        $routes = $this->router->getRoutesByMethod();
        foreach ($routes as $method => $routeList) {
            foreach ($routeList as $route) {
                $name = $route["name"] ? " ({$route["name"]})" : "";
                $middleware =
                    $route["middleware_count"] > 0
                        ? " +{$route["middleware_count"]}mw"
                        : "";
                echo sprintf(
                    "%-7s %-30s%s%s\n",
                    $method,
                    $route["pattern"],
                    $name,
                    $middleware
                );
            }
        }
        echo str_repeat("-", 60) . "\n\n";
    }

    /**
     * Debug: Log route matches
     */
    private function logRouteMatch(
        ServerRequestInterface $request,
        RouteMatch $match
    ): void {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $pattern = $match->route->pattern;
        $params = !empty($match->params) ? json_encode($match->params) : "none";

        echo "Route matched: {$method} {$path} -> {$pattern} (params: {$params})\n";
    }

    // Default handlers
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

    private function parseRequest(TCPStream $client): Generator
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
        if (
            !preg_match('/^(\w+)\s+(\S+)\s+HTTP\/(\d\.\d)$/', $line, $matches)
        ) {
            return null;
        }
        return [$matches[1], $matches[2], $matches[3]];
    }

    private function parseHeaders(TCPStream $client): Generator
    {
        $headers = [];
        while (true) {
            $line = yield from $client->readUntil("\r\n")->unwrap();
            if (empty($line)) {
                break;
            }

            if (str_contains($line, ":")) {
                [$name, $value] = explode(":", $line, 2);
                $name = trim($name);
                $value = trim($value);
                $headers[strtolower($name)][] = $value;
            }
        }
        return $headers;
    }

    private function parseBody(TCPStream $client, array $headers): Generator
    {
        $contentLength = (int) ($headers["content-length"][0] ?? 0);

        if ($contentLength > $this->config->maxRequestSize) {
            throw new HttpException("Request body too large", 413);
        }

        $bodyContent =
            $contentLength > 0
                ? yield from $client->read($contentLength)->unwrap()
                : "";

        return Stream::create($bodyContent);
    }

    private function buildServerParams(
        string $method,
        string $target,
        string $version,
        TCPStream $client
    ): array {
        return [
            "REQUEST_METHOD" => $method,
            "REQUEST_URI" => $target,
            "SERVER_PROTOCOL" => "HTTP/$version",
            "REMOTE_ADDR" => $client->peerAddr(),
            "REQUEST_TIME" => time(),
            "REQUEST_TIME_FLOAT" => microtime(true),
        ];
    }

    private function enrichRequest(
        ServerRequestInterface $request,
        Uri $uri,
        string $body
    ): ServerRequestInterface {
        if ($uri->getQuery()) {
            $queryParams = [];
            parse_str($uri->getQuery(), $queryParams);
            $request = $request->withQueryParams($queryParams);
        }

        if ($request->getMethod() === "POST" && $this->isFormData($request)) {
            $parsedBody = [];
            parse_str($body, $parsedBody);
            $request = $request->withParsedBody($parsedBody);
        }

        return $request;
    }

    private function sendResponse(
        TCPStream $client,
        ResponseInterface $response
    ): Generator {
        $statusLine = sprintf(
            "HTTP/%s %d %s\r\n",
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        $headers = $this->buildResponseHeaders($response);
        $body = $this->getResponseBody($response);

        $httpResponse = $statusLine . $headers . "\r\n" . $body;
        yield from $client->write($httpResponse)->unwrap();
    }

    private function buildResponseHeaders(ResponseInterface $response): string
    {
        $headers = "";

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers .= "$name: $value\r\n";
            }
        }

        $body = $response->getBody();
        if (
            !$response->hasHeader("Content-Length") &&
            $body->getSize() !== null
        ) {
            $headers .= "Content-Length: " . $body->getSize() . "\r\n";
        }

        if (!$response->hasHeader("Server")) {
            $headers .= "Server: VOsaka-HTTP/2.0\r\n";
        }

        if (!$response->hasHeader("Date")) {
            $headers .= "Date: " . gmdate("D, d M Y H:i:s T") . "\r\n";
        }

        return $headers;
    }

    private function getResponseBody(ResponseInterface $response): string
    {
        $body = $response->getBody();
        if ($body->getSize() > 0) {
            $body->rewind();
            return $body->getContents();
        }
        return "";
    }

    private function convertToResponse(mixed $result): ResponseInterface
    {
        return match (true) {
            $result instanceof ResponseInterface => $result,
            is_string($result) => Response::text($result),
            is_array($result) || is_object($result) => Response::json($result),
            default => new Response(200, [], Stream::create((string) $result)),
        };
    }

    private function parseUri(string $target): Uri
    {
        return str_starts_with($target, "http")
            ? new Uri($target)
            : new Uri("http://localhost$target");
    }

    private function isFormData(ServerRequestInterface $request): bool
    {
        $contentType = $request->getHeaderLine("Content-Type");
        return str_contains($contentType, "application/x-www-form-urlencoded");
    }

    private function shouldKeepAlive(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): bool {
        $connection = strtolower($request->getHeaderLine("Connection"));
        return (!in_array($connection, ["close", ""]) &&
            !$response->hasHeader("Connection")) ||
            strtolower($response->getHeaderLine("Connection")) !== "close";
    }
}
