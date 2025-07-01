<?php

declare(strict_types=1);

namespace vosaka\http\router;

/**
 * Route match result
 */
readonly class RouteMatch
{
    public function __construct(
        public RouteDefinition $route,
        public array $params
    ) {
    }
}
