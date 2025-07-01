<?php

declare(strict_types=1);

namespace vosaka\http\server;

use Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use vosaka\http\exceptions\HttpException;
use vosaka\http\message\Response;
use vosaka\http\message\Stream;
use vosaka\http\middleware\MiddlewareStack;
use vosaka\http\router\RouteMatch;
use vosaka\http\router\Router;

final class RequestProcessor
{
    private Router $router;
    private MiddlewareStack $middlewareStack;
    private ErrorHandlerManager $errorHandlers;
    private bool $debugMode;

    public function __construct(
        Router $router,
        MiddlewareStack $middlewareStack,
        ErrorHandlerManager $errorHandlers,
        bool $debugMode = false
    ) {
        $this->router = $router;
        $this->middlewareStack = $middlewareStack;
        $this->errorHandlers = $errorHandlers;
        $this->debugMode = $debugMode;
    }

    public function processRequest(
        ServerRequestInterface $request
    ): ResponseInterface {
        try {
            // Use router's handle method directly which includes caching
            $response = $this->middlewareStack->build(
                fn(ServerRequestInterface $req) => $this->router->handle($req)
            )($request);

            // Handle generator responses from middleware/router
            if ($response instanceof Generator) {
                $finalResponse = null;
                foreach ($response as $value) {
                    $finalResponse = $value;
                }
                $response = $finalResponse;
            }

            return $response instanceof ResponseInterface
                ? $response
                : $this->convertToResponse($response);
        } catch (HttpException $e) {
            // Handle 404 and 405 errors
            if ($e->getCode() === 404) {
                $allowedMethods = $this->findAllowedMethods($request);
                if (!empty($allowedMethods)) {
                    return $this->errorHandlers->handleMethodNotAllowed(
                        $request,
                        $allowedMethods
                    );
                }
                return $this->errorHandlers->handleNotFound($request);
            }
            return $this->errorHandlers->handleError($e, $request);
        } catch (Throwable $e) {
            if ($this->debugMode) {
                echo "Unhandled error: {$e->getMessage()}\n";
                echo "File: {$e->getFile()}:{$e->getLine()}\n";
            }
            return $this->errorHandlers->handleError($e, $request);
        }
    }

    private function findAllowedMethods(ServerRequestInterface $request): array
    {
        $allowedMethods = [];
        $path = $request->getUri()->getPath();

        foreach ($this->router->getRoutes() as $route) {
            if (preg_match($route->compiled, $path)) {
                $allowedMethods[] = $route->method;
            }
        }

        return array_unique($allowedMethods);
    }

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

    private function convertToResponse(mixed $result): ResponseInterface
    {
        return match (true) {
            $result instanceof ResponseInterface => $result,
            is_string($result) => Response::text($result),
            is_array($result) || is_object($result) => Response::json($result),
            default => new Response(200, [], Stream::create((string) $result)),
        };
    }
}
