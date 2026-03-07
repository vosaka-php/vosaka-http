<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use vosaka\foroutines\Async;
use vosaka\http\exceptions\HttpException;
use vosaka\http\message\Response;
use vosaka\http\message\Stream;
use vosaka\http\middleware\MiddlewareStack;
use vosaka\http\router\RouteDefinition;
use vosaka\http\router\Router;

/**
 * RequestProcessor — vosaka-foroutines (Fiber-based).
 *
 * Runs the middleware stack + router handler synchronously inside the
 * caller's fiber. If a route handler returns an Async, it is awaited
 * so handlers can do their own async I/O (DB queries, upstream calls, etc.)
 * without blocking the event loop.
 *
 *   // Sync handler — works as-is
 *   $router->get('/ping', fn($req) => Response::json(['pong' => true]));
 *
 *   // Async handler — also works
 *   $router->get('/data', function ($req) {
 *       $body = AsyncIO::httpGet('https://api.example.com/data')->await();
 *       return Response::json(json_decode($body, true));
 *   });
 */
final class RequestProcessor
{
    public function __construct(
        private readonly Router             $router,
        private readonly MiddlewareStack    $middlewareStack,
        private readonly ErrorHandlerManager $errorHandlers,
        private readonly bool               $debugMode = false,
    ) {}

    // ── Public ───────────────────────────────────────────────────────────────

    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $handler = fn(ServerRequestInterface $req) => $this->router->handle($req);
            $result  = ($this->middlewareStack->build($handler))($request);

            return $this->resolveResult($result);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                $allowed = $this->findAllowedMethods($request);
                if (!empty($allowed)) {
                    return $this->errorHandlers->handleMethodNotAllowed($request, $allowed);
                }
                return $this->errorHandlers->handleNotFound($request);
            }
            return $this->errorHandlers->handleError($e, $request);
        } catch (Throwable $e) {
            if ($this->debugMode) {
                echo "[RequestProcessor] {$e->getMessage()} @ {$e->getFile()}:{$e->getLine()}\n";
            }
            return $this->errorHandlers->handleError($e, $request);
        }
    }

    // ── Private ──────────────────────────────────────────────────────────────

    /**
     * Resolve whatever the route handler returned into a ResponseInterface.
     *
     * Supported handler return types:
     *   ResponseInterface  → returned as-is
     *   Async              → awaited, then resolved recursively
     *   string             → Response::text()
     *   array | object     → Response::json()
     *   mixed              → cast to string, wrapped in 200 text response
     */
    private function resolveResult(mixed $result): ResponseInterface
    {
        // Async handler — await it inside the current fiber
        if ($result instanceof Async) {
            return $this->resolveResult($result->wait());
        }

        return match (true) {
            $result instanceof ResponseInterface    => $result,
            is_string($result)                      => Response::text($result),
            is_array($result) || is_object($result) => Response::json($result),
            default => new Response(200, [], Stream::create((string) $result)),
        };
    }

    /**
     * Check which HTTP methods are registered for this path
     * (used to return a proper 405 with an Allow header).
     *
     * @return string[]
     */
    private function findAllowedMethods(ServerRequestInterface $request): array
    {
        $path    = $request->getUri()->getPath();
        $allowed = [];

        foreach ($this->router->getRoutes() as $route) {
            /** @var RouteDefinition $route */
            if (preg_match($route->compiled->getRegex(), $path)) {
                $allowed[] = $route->method;
            }
        }

        return array_unique($allowed);
    }
}
