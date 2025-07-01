<?php

declare(strict_types=1);

namespace vosaka\http\message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/**
 * PSR-7 Response implementation for HTTP messages.
 *
 * This class represents an HTTP response message with status code, reason phrase,
 * headers, and body. It implements the PSR-7 ResponseInterface for compatibility
 * with HTTP message standards.
 */
final class Response implements ResponseInterface
{
    private int $statusCode;
    private string $reasonPhrase;
    private string $protocolVersion = '1.1';
    private array $headers = [];
    private array $headerNames = [];
    private StreamInterface $body;

    /**
     * @var array<int, string>
     */
    private static array $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        StreamInterface|string|null $body = null,
        string $protocolVersion = '1.1',
        string $reasonPhrase = ''
    ) {
        $this->statusCode = $this->validateStatusCode($statusCode);
        $this->setHeaders($headers);
        $this->body = $this->initializeBody($body);
        $this->protocolVersion = $protocolVersion;
        $this->reasonPhrase = $reasonPhrase !== '' ? $reasonPhrase : (self::$phrases[$statusCode] ?? '');
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $code = $this->validateStatusCode($code);

        if ($code === $this->statusCode && $reasonPhrase === $this->reasonPhrase) {
            return $this;
        }

        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase !== '' ? $reasonPhrase : (self::$phrases[$code] ?? '');
        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): ResponseInterface
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
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): ResponseInterface
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

    public function withAddedHeader(string $name, $value): ResponseInterface
    {
        $this->validateHeaderName($name);
        $value = $this->validateAndTrimHeaderValues($value);
        $normalized = strtolower($name);

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            $header = $new->headerNames[$normalized];
            $new->headers[$header] = array_merge($new->headers[$header], $value);
        } else {
            $new->headerNames[$normalized] = $name;
            $new->headers[$name] = $value;
        }

        return $new;
    }

    public function withoutHeader(string $name): ResponseInterface
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

    public function withBody(StreamInterface $body): ResponseInterface
    {
        if ($body === $this->body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    private function validateStatusCode(int $code): int
    {
        if ($code < 100 || $code >= 600) {
            throw new InvalidArgumentException('Status code must be an integer between 100 and 599');
        }

        return $code;
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
                $this->headers[$header] = array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
    }

    private function validateHeaderName(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string');
        }

        if (!preg_match('/^[!#$%&\'*+\-.0-9A-Z^_`a-z|~]+$/', $name)) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string');
        }
    }

    private function validateAndTrimHeaderValues($values): array
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        if (empty($values)) {
            throw new InvalidArgumentException('Header values must be a string or array of strings, cannot be empty');
        }

        return array_map(function ($value) {
            if ((!is_string($value) && !is_numeric($value)) ||
                preg_match("@^[ \t\n\r]*+$@", (string) $value)) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings');
            }

            return trim((string) $value, " \t");
        }, array_values($values));
    }

    private function initializeBody($body): StreamInterface
    {
        if ($body === null) {
            return Stream::create('');
        }

        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (is_string($body)) {
            return Stream::create($body);
        }

        throw new InvalidArgumentException('Body must be a string, stream or null');
    }

    /**
     * Create a JSON response.
     */
    public static function json(mixed $data, int $statusCode = 200, array $headers = []): self
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $headers['Content-Type'] = 'application/json';

        return new self($statusCode, $headers, $json);
    }

    /**
     * Create an HTML response.
     */
    public static function html(string $html, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'text/html; charset=utf-8';

        return new self($statusCode, $headers, $html);
    }

    /**
     * Create a text response.
     */
    public static function text(string $text, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'text/plain; charset=utf-8';

        return new self($statusCode, $headers, $text);
    }

    /**
     * Create a redirect response.
     */
    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self($statusCode, ['Location' => $url]);
    }
}
