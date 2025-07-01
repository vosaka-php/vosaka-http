<?php

declare(strict_types=1);

namespace vosaka\http\middleware;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for HTTP middleware components.
 *
 * Middleware components can process requests before they reach the handler
 * and/or process responses before they are sent to the client.
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface $request The request to process
     * @param callable $next The next middleware/handler in the chain
     * @return ResponseInterface|null Return a response to short-circuit, or null to continue
     */
    public function process(
        ServerRequestInterface $request,
        callable $next
    ): ?ResponseInterface;
}
