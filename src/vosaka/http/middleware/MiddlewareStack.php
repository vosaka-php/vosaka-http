<?php

declare(strict_types=1);

namespace vosaka\http\middleware;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware stack for composing middleware layers
 */
class MiddlewareStack
{
    private array $middleware = [];

    public function push(MiddlewareInterface|Closure $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function build(Closure $finalHandler): Closure
    {
        $handler = $finalHandler;

        // Build middleware chain in reverse order
        for ($i = count($this->middleware) - 1; $i >= 0; $i--) {
            $middleware = $this->middleware[$i];
            $nextHandler = $handler;

            if ($middleware instanceof MiddlewareInterface) {
                $handler = fn(
                    ServerRequestInterface $request
                ) => $middleware->process($request, $nextHandler);
            } elseif ($middleware instanceof Closure) {
                $handler = fn(ServerRequestInterface $request) => $middleware(
                    $request,
                    $nextHandler
                );
            }
        }

        return $handler;
    }
}
