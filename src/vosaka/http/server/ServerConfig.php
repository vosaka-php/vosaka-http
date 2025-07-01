<?php

declare(strict_types=1);

namespace vosaka\http\server;

/**
 * Server configuration
 */
class ServerConfig
{
    public function __construct(
        public readonly int $maxRequestSize = 65536,
        public readonly int $keepAliveTimeout = 30,
        public readonly int $requestTimeout = 30,
        public readonly bool $debug = false
    ) {}
}
