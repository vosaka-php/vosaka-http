<?php

declare(strict_types=1);

namespace vosaka\http\server;

/**
 * Server configuration
 */
class ServerConfig
{
    public function __construct(
        public int $maxRequestSize = 1048576, // 1MB for larger requests
        public float $keepAliveTimeout = 3.0, // Shorter timeout for high throughput
        public float $requestTimeout = 3.0, // Shorter timeout
        public bool $debug = false
    ) {}
}
