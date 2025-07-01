<?php

declare(strict_types=1);

namespace vosaka\http\message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UploadedFileInterface;
use InvalidArgumentException;

/**
 * PSR-7 ServerRequest implementation for HTTP messages.
 *
 * This class represents an HTTP server request with additional server-side
 * specific data such as query parameters, parsed body, uploaded files,
 * and server parameters.
 */
final class ServerRequest implements ServerRequestInterface
{
    private string $method;
    private UriInterface $uri;
    private string $protocolVersion = "1.1";
    private array $headers = [];
    private array $headerNames = [];
    private StreamInterface $body;
    private ?string $requestTarget = null;
    private array $serverParams = [];
    private array $cookieParams = [];
    private array $queryParams = [];
    private array $uploadedFiles = [];
    private mixed $parsedBody = null;
    private array $attributes = [];

    /**
     * @var array<string, bool>
     */
    private static array $validMethods = [
        "GET" => true,
        "POST" => true,
        "PUT" => true,
        "DELETE" => true,
        "HEAD" => true,
        "OPTIONS" => true,
        "PATCH" => true,
        "TRACE" => true,
        "CONNECT" => true,
    ];

    public function __construct(
        string $method,
        UriInterface|string $uri,
        array $headers = [],
        StreamInterface|string|null $body = null,
        string $protocolVersion = "1.1",
        array $serverParams = []
    ) {
        $this->method = $this->validateMethod($method);
        $this->uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        $this->setHeaders($headers);
        $this->body = $this->initializeBody($body);
        $this->protocolVersion = $protocolVersion;
        $this->serverParams = $serverParams;

        // Parse query parameters from URI
        $this->queryParams = [];
        if ($this->uri->getQuery() !== "") {
            parse_str($this->uri->getQuery(), $this->queryParams);
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): ServerRequestInterface
    {
        $method = $this->validateMethod($method);

        if ($this->method === $method) {
            return $this;
        }

        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(
        UriInterface $uri,
        bool $preserveHost = false
    ): ServerRequestInterface {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$this->hasHeader("Host")) {
            $new->updateHostFromUri();
        }

        // Update query parameters when URI changes
        $new->queryParams = [];
        if ($uri->getQuery() !== "") {
            parse_str($uri->getQuery(), $new->queryParams);
        }

        return $new;
    }

    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target === "") {
            $target = "/";
        }

        if ($this->uri->getQuery() !== "") {
            $target .= "?" . $this->uri->getQuery();
        }

        return $target;
    }

    public function withRequestTarget(
        string $requestTarget
    ): ServerRequestInterface {
        if ($this->requestTarget === $requestTarget) {
            return $this;
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): ServerRequestInterface
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    public function getHeader(string $name): array
    {
        $header = strtolower($name);
        if (!isset($this->headerNames[$header])) {
            return [];
        }

        $header = $this->headerNames[$header];
        return $this->headers[$header];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(", ", $this->getHeader($name));
    }

    public function withHeader(string $name, $value): ServerRequestInterface
    {
        $this->validateHeaderName($name);
        $value = $this->validateAndTrimHeaderValues($value);
        $normalized = strtolower($name);

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }
        $new->headerNames[$normalized] = $name;
        $new->headers[$name] = $value;

        return $new;
    }

    public function withAddedHeader(
        string $name,
        $value
    ): ServerRequestInterface {
        $this->validateHeaderName($name);
        $value = $this->validateAndTrimHeaderValues($value);
        $normalized = strtolower($name);

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            $header = $new->headerNames[$normalized];
            $new->headers[$header] = array_merge(
                $new->headers[$header],
                $value
            );
        } else {
            $new->headerNames[$normalized] = $name;
            $new->headers[$name] = $value;
        }

        return $new;
    }

    public function withoutHeader(string $name): ServerRequestInterface
    {
        $normalized = strtolower($name);
        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $header = $this->headerNames[$normalized];

        $new = clone $this;
        unset($new->headers[$header], $new->headerNames[$normalized]);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): ServerRequestInterface
    {
        if ($body === $this->body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(
        array $uploadedFiles
    ): ServerRequestInterface {
        $this->validateUploadedFiles($uploadedFiles);

        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    public function getParsedBody(): mixed
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        if (!isset($this->attributes[$name])) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    private function validateMethod(string $method): string
    {
        if ($method === "") {
            throw new InvalidArgumentException(
                "Method must be a non-empty string"
            );
        }

        if (!isset(self::$validMethods[$method])) {
            if (!preg_match('/^[!#$%&\'*+\-.0-9A-Z^_`a-z|~]+$/', $method)) {
                throw new InvalidArgumentException("Invalid HTTP method");
            }
        }

        return $method;
    }

    private function setHeaders(array $headers): void
    {
        $this->headerNames = [];
        $this->headers = [];

        foreach ($headers as $header => $value) {
            $this->validateHeaderName($header);
            $value = $this->validateAndTrimHeaderValues($value);
            $normalized = strtolower($header);
            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];
                $this->headers[$header] = array_merge(
                    $this->headers[$header],
                    $value
                );
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
    }

    private function validateHeaderName(string $name): void
    {
        if ($name === "") {
            throw new InvalidArgumentException(
                "Header name must be an RFC 7230 compatible string"
            );
        }

        if (!preg_match('/^[!#$%&\'*+\-.0-9A-Z^_`a-z|~]+$/', $name)) {
            throw new InvalidArgumentException(
                "Header name must be an RFC 7230 compatible string"
            );
        }
    }

    private function validateAndTrimHeaderValues($values): array
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        if (empty($values)) {
            throw new InvalidArgumentException(
                "Header values must be a string or array of strings, cannot be empty"
            );
        }

        return array_map(function ($value) {
            if (
                (!is_string($value) && !is_numeric($value)) ||
                preg_match("@^[ \t\n\r]*+$@", (string) $value)
            ) {
                throw new InvalidArgumentException(
                    "Header values must be RFC 7230 compatible strings"
                );
            }

            return trim((string) $value, " \t");
        }, array_values($values));
    }

    private function initializeBody($body): StreamInterface
    {
        if ($body === null) {
            return Stream::create("");
        }

        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (is_string($body)) {
            return Stream::create($body);
        }

        throw new InvalidArgumentException(
            "Body must be a string, stream or null"
        );
    }

    private function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();
        if ($host === "") {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ":" . $port;
        }

        if (isset($this->headerNames["host"])) {
            $header = $this->headerNames["host"];
        } else {
            $header = "Host";
            $this->headerNames["host"] = "Host";
        }

        $this->headers[$header] = [$host];
    }

    private function validateUploadedFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
            } elseif (!$file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException("Invalid uploaded file");
            }
        }
    }

    /**
     * Create a ServerRequest from global variables.
     */
    public static function fromGlobals(): self
    {
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
        $headers = getallheaders() ?: [];
        $uri = self::getUriFromGlobals();
        $body = Stream::createFromResource(fopen("php://input", "r"));
        $protocol = isset($_SERVER["SERVER_PROTOCOL"])
            ? str_replace("HTTP/", "", $_SERVER["SERVER_PROTOCOL"])
            : "1.1";

        $request = new self(
            $method,
            $uri,
            $headers,
            $body,
            $protocol,
            $_SERVER
        );

        return $request
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST);
    }

    private static function getUriFromGlobals(): Uri
    {
        $uri = "";

        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") {
            $uri .= "https://";
        } else {
            $uri .= "http://";
        }

        if (isset($_SERVER["HTTP_HOST"])) {
            $uri .= $_SERVER["HTTP_HOST"];
        } elseif (isset($_SERVER["SERVER_NAME"])) {
            $uri .= $_SERVER["SERVER_NAME"];
            if (
                isset($_SERVER["SERVER_PORT"]) &&
                $_SERVER["SERVER_PORT"] !== "80" &&
                $_SERVER["SERVER_PORT"] !== "443"
            ) {
                $uri .= ":" . $_SERVER["SERVER_PORT"];
            }
        }

        $uri .= $_SERVER["REQUEST_URI"] ?? "/";

        return new Uri($uri);
    }
}
