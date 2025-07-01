<?php

declare(strict_types=1);

namespace vosaka\http\router;

use Closure;

/**
 * Route definition data class
 */
final class RouteDefinition
{
    public function __construct(
        public string $method,
        public string $pattern,
        public Closure $handler,
        public array $middleware,
        public CompiledRoute $compiled,
        public ?string $name = null
    ) {
    }
}
