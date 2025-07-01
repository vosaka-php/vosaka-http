<?php

declare(strict_types=1);

namespace vosaka\http\server;

use vosaka\http\router\Router;

final class ServerDebugHelper
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function printRouteTable(): void
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
}
