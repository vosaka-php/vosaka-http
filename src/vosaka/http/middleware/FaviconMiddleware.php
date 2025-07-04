<?php

declare(strict_types=1);

namespace vosaka\http\middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use vosaka\http\message\Response;

/**
 * Favicon middleware.
 *
 * This middleware handles favicon.ico requests automatically to prevent
 * 404 errors and reduce server load from browser favicon requests.
 */
final class FaviconMiddleware implements MiddlewareInterface
{
    private ?string $faviconPath = null;
    private ?string $faviconData = null;
    private bool $faviconLoaded = false;
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge(
            [
                "path" => "/favicon.ico",
                "cache_control" => "public, max-age=86400", // 24 hours
                "expires" => 86400, // 24 hours
                "content_type" => "image/x-icon",
                "return_204" => true, // Return 204 No Content if no favicon
            ],
            $options
        );

        // Set favicon path if provided
        if (
            isset($options["favicon_path"]) &&
            is_string($options["favicon_path"])
        ) {
            $this->faviconPath = $options["favicon_path"];
        }

        // Set favicon data if provided
        if (
            isset($options["favicon_data"]) &&
            is_string($options["favicon_data"])
        ) {
            $this->faviconData = $options["favicon_data"];
            $this->faviconLoaded = true;
        }
    }

    public function process(
        ServerRequestInterface $request,
        callable $next
    ): ?ResponseInterface {
        // Check if this is a favicon request
        if (!$this->isFaviconRequest($request)) {
            return $next($request);
        }

        // Handle favicon request
        return $this->handleFaviconRequest($request);
    }

    private function isFaviconRequest(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        return $path === $this->options["path"];
    }

    private function handleFaviconRequest(
        ServerRequestInterface $request
    ): ResponseInterface {
        // Load favicon if not loaded yet
        if (!$this->faviconLoaded) {
            $this->loadFavicon();
        }

        // Return favicon if available
        if ($this->faviconData !== null) {
            return $this->createFaviconResponse();
        }

        // Return 204 No Content or 404 based on configuration
        return $this->createEmptyResponse();
    }

    private function loadFavicon(): void
    {
        $this->faviconLoaded = true;

        if ($this->faviconPath && file_exists($this->faviconPath)) {
            $this->faviconData = file_get_contents($this->faviconPath);
        }
    }

    private function createFaviconResponse(): ResponseInterface
    {
        $headers = [
            "Content-Type" => $this->options["content_type"],
            "Content-Length" => (string) strlen($this->faviconData),
            "Cache-Control" => $this->options["cache_control"],
        ];

        // Add Expires header if configured
        if ($this->options["expires"] > 0) {
            $headers["Expires"] = gmdate(
                "D, d M Y H:i:s T",
                time() + $this->options["expires"]
            );
        }

        return new Response(200, $headers, $this->faviconData);
    }

    private function createEmptyResponse(): ResponseInterface
    {
        if ($this->options["return_204"]) {
            // Return 204 No Content - browser friendly
            return new Response(204, [
                "Cache-Control" => $this->options["cache_control"],
                "Expires" => gmdate(
                    "D, d M Y H:i:s T",
                    time() + $this->options["expires"]
                ),
            ]);
        }

        // Return 404 Not Found
        return new Response(
            404,
            [
                "Content-Type" => "text/plain; charset=utf-8",
                "Content-Length" => "9",
            ],
            "Not Found"
        );
    }

    /**
     * Set favicon data directly
     */
    public function setFaviconData(string $data): self
    {
        $this->faviconData = $data;
        $this->faviconLoaded = true;
        return $this;
    }

    /**
     * Set favicon file path
     */
    public function setFaviconPath(string $path): self
    {
        $this->faviconPath = $path;
        $this->faviconLoaded = false;
        return $this;
    }

    /**
     * Create favicon middleware that returns 204 No Content
     */
    public static function noContent(): self
    {
        return new self([
            "return_204" => true,
            "cache_control" => "public, max-age=86400",
            "expires" => 86400,
        ]);
    }

    /**
     * Create favicon middleware that returns 404 Not Found
     */
    public static function notFound(): self
    {
        return new self([
            "return_204" => false,
            "cache_control" => "no-cache",
            "expires" => 0,
        ]);
    }

    /**
     * Create favicon middleware with custom favicon file
     */
    public static function fromFile(string $filePath): self
    {
        return new self([
            "favicon_path" => $filePath,
            "return_204" => false,
            "cache_control" => "public, max-age=86400",
            "expires" => 86400,
        ]);
    }

    /**
     * Create favicon middleware with favicon data
     */
    public static function fromData(string $data): self
    {
        return new self([
            "favicon_data" => $data,
            "return_204" => false,
            "cache_control" => "public, max-age=86400",
            "expires" => 86400,
        ]);
    }

    /**
     * Create favicon middleware with custom path
     */
    public static function customPath(string $path): self
    {
        return new self([
            "path" => $path,
            "return_204" => true,
            "cache_control" => "public, max-age=86400",
            "expires" => 86400,
        ]);
    }
}
