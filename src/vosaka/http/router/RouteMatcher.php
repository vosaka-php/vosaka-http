<?php

declare(strict_types=1);

namespace vosaka\http\router;

use Psr\Http\Message\ServerRequestInterface;

final class RouteMatcher
{
    public function findMatch(
        array $routes,
        ServerRequestInterface $request
    ): ?RouteMatch {
        $path = rtrim($request->getUri()->getPath(), "/") ?: "/";

        foreach ($routes as $route) {
            $params = $this->matchPattern($route->compiled, $path);
            if ($params !== null) {
                return new RouteMatch($route, $params);
            }
        }

        return null;
    }

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
}
