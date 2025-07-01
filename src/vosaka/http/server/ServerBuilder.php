<?php

declare(strict_types=1);

namespace vosaka\http\server;

use venndev\vosaka\core\Result;

/**
 * Server builder for fluent configuration
 */
class ServerBuilder
{
    public function __construct(
        private HttpServer $server,
        private string $address
    ) {}

    public function serve(): Result
    {
        return $this->server->serve($this->address);
    }

    public function with_config(Router $router, ServerConfig $config): self
    {
        return new self(new HttpServer($router, $config), $this->address);
    }
}
