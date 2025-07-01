<?php

declare(strict_types=1);

namespace vosaka\http\router;

use InvalidArgumentException;
use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use vosaka\http\exceptions\HttpException;
use vosaka\http\message\Response;
use vosaka\http\message\ResponseConverter;
use vosaka\http\middleware\MiddlewareInterface;
use vosaka\http\middleware\MiddlewareStack;
use vosaka\http\utils\UrlGenerator;

/**
 * Enhanced Router with comprehensive parameter support
 * Now refactored into smaller, focused classes
 */
final class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $globalMiddleware = [];
    private array $methodRoutes = [];
    private array $routeCache = [];
    private int $cacheSize = 0;
    private const MAX_CACHE_SIZE = 8000; // Maximum cache size
    private string $prefix = "";

    private PatternCompiler $compiler;
    private RouteMatcher $matcher;
    private UrlGenerator $urlGenerator;
    private ResponseConverter $responseConverter;

    public function __construct(
        private ?MiddlewareStack $middlewareStack = null
    ) {
        $this->middlewareStack ??= new MiddlewareStack();
        $this->compiler = new PatternCompiler();
        $this->matcher = new RouteMatcher();
        $this->responseConverter = new ResponseConverter();
        $this->updateUrlGenerator();
    }

    public function getMatcher(): RouteMatcher
    {
        return $this->matcher;
    }

    public static function new(): self
    {
        return new self();
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
            compiled: $this->compiler->compile($fullPattern),
            name: $name
        );

        $this->routes[] = $route;
        $this->methodRoutes[strtoupper($method)][] = $route;

        if ($name !== null) {
            $this->namedRoutes[$name] = $route;
            $this->updateUrlGenerator();
        }

        return $this;
    }

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

    public function middleware(MiddlewareInterface|Closure ...$middleware): self
    {
        if (empty($this->routes)) {
            throw new InvalidArgumentException("No route to add middleware to");
        }

        $lastRoute = &$this->routes[array_key_last($this->routes)];
        $lastRoute->middleware = [...$lastRoute->middleware, ...$middleware];

        return $this;
    }

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

    public function layer(MiddlewareInterface|Closure $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    public function mount(string $prefix, Router $router): self
    {
        foreach ($router->routes as $route) {
            $newPattern = rtrim($prefix, "/") . $route->pattern;
            $newRoute = new RouteDefinition(
                method: $route->method,
                pattern: $newPattern,
                handler: $route->handler,
                middleware: [...$this->globalMiddleware, ...$route->middleware],
                compiled: $this->compiler->compile($newPattern),
                name: $route->name
            );

            $this->routes[] = $newRoute;

            if ($route->name !== null) {
                $this->namedRoutes[$route->name] = $newRoute;
            }
        }

        $this->updateUrlGenerator();
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $cacheKey = $method . ':' . $path;
        
        // Check cache first - hot path
        if (isset($this->routeCache[$cacheKey])) {
            $match = $this->routeCache[$cacheKey];
        } else {
            $routes = $this->getRoutes($method);
            $match = $this->matcher->findMatch($routes, $request);
            
            // Cache the result if we have a match
            if ($match !== null) {
                if ($this->cacheSize >= self::MAX_CACHE_SIZE) {
                    // Simple LRU: remove first entry when cache is full
                    $firstKey = array_key_first($this->routeCache);
                    unset($this->routeCache[$firstKey]);
                    $this->cacheSize--;
                }
                $this->routeCache[$cacheKey] = $match;
                $this->cacheSize++;
            }
        }

        if ($match === null) {
            throw new HttpException("Not Found", 404);
        }

        // Fast path for simple routes (no params, no middleware)
        if (empty($match->params) && empty($match->route->middleware) && empty($this->globalMiddleware)) {
            $request = $request->withAttribute("_route", $match->route);
            $response = ($match->route->handler)($request);
            return $this->responseConverter->convert($response);
        }

        // Add route parameters to request attributes (optimized)
        if (!empty($match->params)) {
            foreach ($match->params as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
            $request = $request->withAttribute("_route_params", $match->params);
        }

        // Add route info for debugging
        $request = $request->withAttribute("_route", $match->route);

        // Build middleware chain
        $handler = $this->buildHandler($match->route, $request);
        $response = $handler($request);

        return $this->responseConverter->convert($response);
    }

    public function url(
        string $name,
        array $params = [],
        array $query = []
    ): string {
        return $this->urlGenerator->generate($name, $params, $query);
    }

    public function hasRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }

    public function getRoutes(?string $method = null): array
    {
        if ($method === null) {
            return $this->routes;
        }
        return $this->methodRoutes[strtoupper($method)] ?? [];
    }

    public function getRoute(string $name): ?RouteDefinition
    {
        return $this->namedRoutes[$name] ?? null;
    }

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

    private function updateUrlGenerator(): void
    {
        $this->urlGenerator = new UrlGenerator($this->namedRoutes);
    }
}
