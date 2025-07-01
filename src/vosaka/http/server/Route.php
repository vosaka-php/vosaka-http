<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use vosaka\http\exceptions\HttpException;
use vosaka\http\middleware\MiddlewareInterface;
use vosaka\http\middleware\MiddlewareStack;

class Route
{
    private array $methods = [];
    private array $handlers = [];
    private array $middleware = [];

    public static function new(): self
    {
        return new self();
    }

    public function get(Closure $handler): self
    {
        return $this->method("GET", $handler);
    }

    public function post(Closure $handler): self
    {
        return $this->method("POST", $handler);
    }

    public function put(Closure $handler): self
    {
        return $this->method("PUT", $handler);
    }

    public function delete(Closure $handler): self
    {
        return $this->method("DELETE", $handler);
    }

    public function patch(Closure $handler): self
    {
        return $this->method("PATCH", $handler);
    }

    public function method(string $method, Closure $handler): self
    {
        $this->methods[] = strtoupper($method);
        $this->handlers[strtoupper($method)] = $handler;
        return $this;
    }

    public function layer(MiddlewareInterface|Closure $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();

        if (!isset($this->handlers[$method])) {
            throw new HttpException("Method Not Allowed", 405);
        }

        $handler = $this->handlers[$method];

        // Apply route-specific middleware
        if (!empty($this->middleware)) {
            $middlewareStack = new MiddlewareStack();
            foreach ($this->middleware as $mw) {
                $middlewareStack->push($mw);
            }
            $handler = $middlewareStack->build($handler);
        }

        return $handler($request);
    }

    public function supportsMethods(): array
    {
        return $this->methods;
    }
}
