<?php

declare(strict_types=1);

namespace vosaka\http\server;

use InvalidArgumentException;
use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use vosaka\http\exceptions\HttpException;
use vosaka\http\message\Response;
use vosaka\http\message\Stream;
use vosaka\http\middleware\MiddlewareInterface;
use vosaka\http\middleware\MiddlewareStack;

/**
 * Enhanced Router with comprehensive parameter support
 *
 * Features:
 * - Required parameters: /users/{id}
 * - Optional parameters: /users/{id?}
 * - Constrained parameters: /users/{id:\d+}
 * - Multiple constraints: /users/{id:\d+}/{slug:[a-z-]+}
 * - Wildcard routes: /files/*
 * - Named route groups
 * - Query parameter handling
 * - Route caching for performance
 */
final class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $globalMiddleware = [];
    private array $compiledCache = [];
    private string $prefix = "";

    public function __construct(
        private ?MiddlewareStack $middlewareStack = null
    ) {
        $this->middlewareStack ??= new MiddlewareStack();
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * Add a route with method, pattern and handler
     */
    public function route(
        string $method,
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        $fullPattern = $this->prefix . $pattern;

        $route = new RouteDefinition(
            method: strtoupper($method),
            pattern: $fullPattern,
            handler: $handler,
            middleware: [],
            compiled: $this->compilePattern($fullPattern),
            name: $name
        );

        $this->routes[] = $route;

        if ($name !== null) {
            $this->namedRoutes[$name] = $route;
        }

        return $this;
    }

    // HTTP method shortcuts
    public function get(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        return $this->route("GET", $pattern, $handler, $name);
    }

    public function post(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        return $this->route("POST", $pattern, $handler, $name);
    }

    public function put(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        return $this->route("PUT", $pattern, $handler, $name);
    }

    public function delete(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        return $this->route("DELETE", $pattern, $handler, $name);
    }

    public function patch(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        return $this->route("PATCH", $pattern, $handler, $name);
    }

    public function head(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        return $this->route("HEAD", $pattern, $handler, $name);
    }

    public function options(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        return $this->route("OPTIONS", $pattern, $handler, $name);
    }

    /**
     * Match multiple methods for the same pattern
     */
    public function match(
        array $methods,
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        foreach ($methods as $method) {
            $this->route($method, $pattern, $handler, $name);
        }
        return $this;
    }

    /**
     * Match any HTTP method
     */
    public function any(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        return $this->match(
            ["GET", "POST", "PUT", "DELETE", "PATCH", "HEAD", "OPTIONS"],
            $pattern,
            $handler,
            $name
        );
    }

    /**
     * Add middleware to the last added route
     */
    public function middleware(MiddlewareInterface|Closure ...$middleware): self
    {
        if (empty($this->routes)) {
            throw new InvalidArgumentException("No route to add middleware to");
        }

        $lastRoute = &$this->routes[array_key_last($this->routes)];
        $lastRoute->middleware = [...$lastRoute->middleware, ...$middleware];

        return $this;
    }

    /**
     * Create a route group with shared prefix and middleware
     */
    public function group(
        string $prefix,
        Closure $callback,
        array $middleware = []
    ): self {
        $originalPrefix = $this->prefix;
        $originalMiddleware = $this->globalMiddleware;

        $this->prefix = $this->prefix . rtrim($prefix, "/");
        $this->globalMiddleware = [...$this->globalMiddleware, ...$middleware];

        // Create RouteGroup for fluent API
        $group = new RouteGroup($this, $this->prefix);
        $callback($group);

        $this->prefix = $originalPrefix;
        $this->globalMiddleware = $originalMiddleware;

        return $this;
    }

    /**
     * Add middleware that applies to all routes
     */
    public function layer(MiddlewareInterface|Closure $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    /**
     * Mount another router with a prefix
     */
    public function mount(string $prefix, Router $router): self
    {
        foreach ($router->routes as $route) {
            $newPattern = rtrim($prefix, "/") . $route->pattern;
            $newRoute = new RouteDefinition(
                method: $route->method,
                pattern: $newPattern,
                handler: $route->handler,
                middleware: [...$this->globalMiddleware, ...$route->middleware],
                compiled: $this->compilePattern($newPattern),
                name: $route->name
            );

            $this->routes[] = $newRoute;

            if ($route->name !== null) {
                $this->namedRoutes[$route->name] = $newRoute;
            }
        }

        return $this;
    }

    /**
     * Handle incoming request and return response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $match = $this->findMatch($request);

        if ($match === null) {
            throw new HttpException("Not Found", 404);
        }

        // Add route parameters to request attributes
        foreach ($match->params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        // Also add route info for debugging
        $request = $request->withAttribute("_route", $match->route);
        $request = $request->withAttribute("_route_params", $match->params);

        // Build middleware chain
        $handler = $this->buildHandler($match->route, $request);

        $response = $handler($request);

        return $response instanceof ResponseInterface
            ? $response
            : $this->convertToResponse($response);
    }

    /**
     * Find matching route for request
     */
    public function findMatch(ServerRequestInterface $request): ?RouteMatch
    {
        $method = $request->getMethod();
        $path = rtrim($request->getUri()->getPath(), "/") ?: "/";

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            $params = $this->matchPattern($route->compiled, $path);
            if ($params !== null) {
                return new RouteMatch($route, $params);
            }
        }

        return null;
    }

    /**
     * Generate URL for named route
     */
    public function url(
        string $name,
        array $params = [],
        array $query = []
    ): string {
        if (!isset($this->namedRoutes[$name])) {
            throw new InvalidArgumentException("Route '{$name}' not found");
        }

        $route = $this->namedRoutes[$name];
        $url = $this->buildUrl($route->pattern, $params);

        // Add query parameters if provided
        if (!empty($query)) {
            $url .= "?" . http_build_query($query);
        }

        return $url;
    }

    /**
     * Check if route exists
     */
    public function hasRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * Get all routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get route by name
     */
    public function getRoute(string $name): ?RouteDefinition
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Get routes organized by method for debugging
     */
    public function getRoutesByMethod(): array
    {
        $routes = [];
        foreach ($this->routes as $route) {
            $routes[$route->method][] = [
                "pattern" => $route->pattern,
                "name" => $route->name,
                "middleware_count" => count($route->middleware),
            ];
        }
        return $routes;
    }

    /**
     * Build middleware handler chain
     */
    private function buildHandler(
        RouteDefinition $route,
        ServerRequestInterface $request
    ): Closure {
        $stack = clone $this->middlewareStack;

        // Add global middleware
        foreach ($this->globalMiddleware as $middleware) {
            $stack->push($middleware);
        }

        // Add route-specific middleware
        foreach ($route->middleware as $middleware) {
            $stack->push($middleware);
        }

        return $stack->build($route->handler);
    }

    /**
     * FIXED: Completely rewritten pattern compilation method
     */
    private function compilePattern(string $pattern): CompiledRoute
    {
        // Use cache for performance
        if (isset($this->compiledCache[$pattern])) {
            return $this->compiledCache[$pattern];
        }

        $params = [];
        $regex = $pattern;
        $hasWildcard = false;

        // Handle wildcards first: /files/* -> captures everything after /files/
        if (str_ends_with($pattern, "/*")) {
            $regex = str_replace("/*", "/(?<wildcard>.*)", $regex);
            $params[] = "wildcard";
            $hasWildcard = true;
        }

        // Process parameter patterns step by step
        $segments = [];
        $currentPos = 0;
        $patternLength = strlen($regex);

        while ($currentPos < $patternLength) {
            // Find next parameter start
            $paramStart = strpos($regex, "{", $currentPos);
            if ($paramStart === false) {
                // No more parameters, add rest as literal
                $segments[] = [
                    "type" => "literal",
                    "value" => substr($regex, $currentPos),
                ];
                break;
            }

            // Add literal part before parameter
            if ($paramStart > $currentPos) {
                $segments[] = [
                    "type" => "literal",
                    "value" => substr(
                        $regex,
                        $currentPos,
                        $paramStart - $currentPos
                    ),
                ];
            }

            // Find parameter end
            $paramEnd = strpos($regex, "}", $paramStart);
            if ($paramEnd === false) {
                throw new InvalidArgumentException(
                    "Unclosed parameter in pattern: {$pattern}"
                );
            }

            // Extract parameter definition
            $paramDef = substr(
                $regex,
                $paramStart + 1,
                $paramEnd - $paramStart - 1
            );
            $currentPos = $paramEnd + 1;

            // Parse parameter definition
            $isOptional = str_ends_with($paramDef, "?");
            if ($isOptional) {
                $paramDef = substr($paramDef, 0, -1);
            }

            $constraint = "[^/]+"; // default constraint
            if (str_contains($paramDef, ":")) {
                [$paramName, $constraint] = explode(":", $paramDef, 2);
            } else {
                $paramName = $paramDef;
            }

            $params[] = $paramName;
            $segments[] = [
                "type" => "param",
                "name" => $paramName,
                "constraint" => $constraint,
                "optional" => $isOptional,
            ];
        }

        // Build final regex from segments
        $regexParts = [];
        foreach ($segments as $segment) {
            if ($segment["type"] === "literal") {
                $regexParts[] = preg_quote($segment["value"], "#");
            } else {
                // parameter segment
                $namedGroup = "(?<{$segment["name"]}>{$segment["constraint"]})";
                if ($segment["optional"]) {
                    $regexParts[] = "(?:/{$namedGroup})?";
                } else {
                    $regexParts[] = $namedGroup;
                }
            }
        }

        $finalRegex = "#^" . implode("", $regexParts) . '$#';

        $compiled = new CompiledRoute($finalRegex, $params, [], $hasWildcard);
        $this->compiledCache[$pattern] = $compiled;

        return $compiled;
    }

    /**
     * Enhanced pattern matching using named capture groups
     */
    private function matchPattern(CompiledRoute $compiled, string $path): ?array
    {
        if (!preg_match($compiled->regex, $path, $matches)) {
            return null;
        }

        $params = [];

        // Extract named captures
        foreach ($compiled->params as $paramName) {
            if (isset($matches[$paramName]) && $matches[$paramName] !== "") {
                // Decode URL-encoded values
                $params[$paramName] = urldecode($matches[$paramName]);
            }
        }

        return $params;
    }

    /**
     * Enhanced URL building with better parameter handling
     */
    private function buildUrl(string $pattern, array $params): string
    {
        $url = $pattern;

        // Sort parameters by length (longest first) to avoid partial replacements
        uksort($params, fn($a, $b) => strlen($b) - strlen($a));

        // Replace constrained parameters first: {id:\d+}
        foreach ($params as $key => $value) {
            $url = preg_replace(
                "/\{" . preg_quote($key, "/") . ":[^}]+\}/",
                urlencode((string) $value),
                $url
            );
        }

        // Replace regular parameters: {id}
        foreach ($params as $key => $value) {
            $url = str_replace(
                "{" . $key . "}",
                urlencode((string) $value),
                $url
            );
        }

        // Remove optional parameters that weren't provided
        $url = preg_replace("/\{[^}]*\?\}/", "", $url);

        // Clean up any double slashes
        $url = preg_replace("#/+#", "/", $url);

        // Check for unreplaced required parameters
        if (preg_match("/\{[^}]+\}/", $url)) {
            preg_match_all("/\{([^}]+)\}/", $url, $missingParams);
            throw new InvalidArgumentException(
                "Missing required parameters: " .
                    implode(", ", $missingParams[1])
            );
        }

        return $url;
    }

    /**
     * Convert handler result to proper response
     */
    private function convertToResponse(mixed $result): ResponseInterface
    {
        return match (true) {
            $result instanceof ResponseInterface => $result,
            is_string($result) => Response::text($result),
            is_array($result) || is_object($result) => Response::json($result),
            is_null($result) => new Response(204), // No Content
            is_bool($result) => Response::text($result ? "true" : "false"),
            is_numeric($result) => Response::text((string) $result),
            default => new Response(200, [], Stream::create((string) $result)),
        };
    }
}
