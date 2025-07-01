<?php

declare(strict_types=1);

namespace vosaka\http\middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use vosaka\http\message\Response;

/**
 * CORS (Cross-Origin Resource Sharing) middleware.
 *
 * This middleware handles CORS preflight requests and adds appropriate
 * CORS headers to responses to enable cross-origin requests from browsers.
 */
final class CorsMiddleware implements MiddlewareInterface
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge(
            [
                "origin" => "*",
                "methods" => [
                    "GET",
                    "POST",
                    "PUT",
                    "DELETE",
                    "PATCH",
                    "OPTIONS",
                ],
                "headers" => [
                    "Content-Type",
                    "Authorization",
                    "X-Requested-With",
                ],
                "credentials" => false,
                "max_age" => 86400, // 24 hours
                "expose_headers" => [],
            ],
            $options
        );
    }

    public function process(
        ServerRequestInterface $request,
        callable $next
    ): ?ResponseInterface {
        // Handle preflight OPTIONS request
        if ($request->getMethod() === "OPTIONS") {
            return $this->handlePreflightRequest($request);
        }

        // Process the request through the next middleware/handler
        $response = $next($request);
        if ($response === null) {
            $response = new Response();
        }

        // Add CORS headers to the response
        return $this->addCorsHeaders($request, $response);
    }

    private function handlePreflightRequest(
        ServerRequestInterface $request
    ): ResponseInterface {
        $response = new Response(204);

        // Add CORS headers for preflight
        $response = $this->addCorsHeaders($request, $response);

        // Add preflight-specific headers
        if (!empty($this->options["methods"])) {
            $response = $response->withHeader(
                "Access-Control-Allow-Methods",
                implode(", ", $this->options["methods"])
            );
        }

        if (!empty($this->options["headers"])) {
            $response = $response->withHeader(
                "Access-Control-Allow-Headers",
                implode(", ", $this->options["headers"])
            );
        }

        if ($this->options["max_age"] > 0) {
            $response = $response->withHeader(
                "Access-Control-Max-Age",
                (string) $this->options["max_age"]
            );
        }

        return $response;
    }

    private function addCorsHeaders(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // Handle Origin header
        $origin = $this->getAllowedOrigin($request);
        if ($origin !== null) {
            $response = $response->withHeader(
                "Access-Control-Allow-Origin",
                $origin
            );
        }

        // Handle credentials
        if ($this->options["credentials"]) {
            $response = $response->withHeader(
                "Access-Control-Allow-Credentials",
                "true"
            );
        }

        // Handle exposed headers
        if (!empty($this->options["expose_headers"])) {
            $response = $response->withHeader(
                "Access-Control-Expose-Headers",
                implode(", ", $this->options["expose_headers"])
            );
        }

        // Add Vary header to indicate that the response varies based on Origin
        if ($this->options["origin"] !== "*") {
            $vary = $response->getHeaderLine("Vary");
            if ($vary === "") {
                $response = $response->withHeader("Vary", "Origin");
            } elseif (!str_contains($vary, "Origin")) {
                $response = $response->withHeader("Vary", $vary . ", Origin");
            }
        }

        return $response;
    }

    private function getAllowedOrigin(ServerRequestInterface $request): ?string
    {
        $requestOrigin = $request->getHeaderLine("Origin");

        if ($requestOrigin === "") {
            return null;
        }

        $allowedOrigin = $this->options["origin"];

        // Allow all origins
        if ($allowedOrigin === "*") {
            return "*";
        }

        // Single origin string
        if (is_string($allowedOrigin)) {
            return $allowedOrigin === $requestOrigin ? $requestOrigin : null;
        }

        // Array of allowed origins
        if (is_array($allowedOrigin)) {
            return in_array($requestOrigin, $allowedOrigin, true)
                ? $requestOrigin
                : null;
        }

        // Callable for custom origin validation
        if (is_callable($allowedOrigin)) {
            return $allowedOrigin($requestOrigin, $request)
                ? $requestOrigin
                : null;
        }

        return null;
    }

    /**
     * Create CORS middleware with permissive settings for development.
     */
    public static function permissive(): self
    {
        return new self([
            "origin" => "*",
            "methods" => [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH",
                "OPTIONS",
                "HEAD",
            ],
            "headers" => ["*"],
            "credentials" => false,
            "max_age" => 86400,
        ]);
    }

    /**
     * Create CORS middleware with strict settings for production.
     */
    public static function strict(array $allowedOrigins = []): self
    {
        return new self([
            "origin" => $allowedOrigins,
            "methods" => ["GET", "POST"],
            "headers" => ["Content-Type", "Authorization"],
            "credentials" => true,
            "max_age" => 3600, // 1 hour
        ]);
    }
}
