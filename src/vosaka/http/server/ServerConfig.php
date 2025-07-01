<?php

declare(strict_types=1);

namespace vosaka\http\server;

/**
 * Server configuration
 */
class ServerConfig
{
    public function __construct(
        public readonly int $maxRequestSize = 1048576, // 1MB for larger requests
        public readonly int $keepAliveTimeout = 3, // Shorter timeout for high throughput
        public readonly int $requestTimeout = 3, // Shorter timeout
        public readonly bool $debug = false
    ) {}
}
