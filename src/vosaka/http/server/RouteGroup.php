<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Closure;
use vosaka\http\middleware\MiddlewareInterface;

/**
 * Route group helper for fluent API
 */
class RouteGroup
{
    public function __construct(
        private Router $router,
        private string $prefix
    ) {}

    public function get(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        $this->router->get($this->prefix . $pattern, $handler, $name);
        return $this;
    }

    public function post(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        $this->router->post($this->prefix . $pattern, $handler, $name);
        return $this;
    }

    public function put(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        $this->router->put($this->prefix . $pattern, $handler, $name);
        return $this;
    }

    public function delete(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        $this->router->delete($this->prefix . $pattern, $handler, $name);
        return $this;
    }

    public function patch(
        string $pattern,
        Closure $handler,
        ?string $name = null
    ): self {
        $this->router->patch($this->prefix . $pattern, $handler, $name);
        return $this;
    }

    public function middleware(MiddlewareInterface|Closure ...$middleware): self
    {
        $this->router->middleware(...$middleware);
        return $this;
    }

    public function group(
        string $prefix,
        Closure $callback,
        array $middleware = []
    ): self {
        $this->router->group($this->prefix . $prefix, $callback, $middleware);
        return $this;
    }
}
