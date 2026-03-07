<?php

declare(strict_types=1);

namespace vosaka\http\server;

/**
 * ServerBuilder — fluent builder for HttpServer.
 *
 * Usage:
 *   $server->bind('0.0.0.0:8080')->serve();
 *   $server->bind('0.0.0.0:8080')->withOptions([...])->serve();
 */
final class ServerBuilder
{
    private array $options = [];

    public function __construct(
        private readonly HttpServer $server,
        private readonly string     $address,
    ) {}

    /**
     * Pass raw stream_socket_server context options.
     * e.g. ['socket' => ['backlog' => 1024]]
     */
    public function withOptions(array $options): self
    {
        $clone          = clone $this;
        $clone->options = $options;
        return $clone;
    }

    /**
     * Start the server — blocks inside RunBlocking until shutdown().
     * Must be called from within main().
     */
    public function serve(): void
    {
        $this->server->serve($this->address, $this->options);
    }
}
