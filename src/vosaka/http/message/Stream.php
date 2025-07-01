<?php

declare(strict_types=1);

namespace vosaka\http\message;

use Psr\Http\Message\StreamInterface;
use Throwable;
use RuntimeException;
use InvalidArgumentException;

/**
 * PSR-7 Stream implementation for HTTP messages.
 *
 * This class provides a stream implementation that can work with various
 * resource types including files, memory streams, and network streams.
 * It implements the PSR-7 StreamInterface for compatibility with HTTP
 * message standards.
 */
final class Stream implements StreamInterface
{
    private mixed $resource;
    private bool $readable = false;
    private bool $writable = false;
    private bool $seekable = false;
    private int|null $size = null;
    private string|null $uri = null;

    /**
     * @var array<string, array<string, bool>>
     */
    private static array $readWriteHash = [
        "read" => [
            "r" => true,
            "w+" => true,
            "r+" => true,
            "x+" => true,
            "c+" => true,
            "rb" => true,
            "w+b" => true,
            "r+b" => true,
            "x+b" => true,
            "c+b" => true,
            "rt" => true,
            "w+t" => true,
            "r+t" => true,
            "x+t" => true,
            "c+t" => true,
            "a+" => true,
            "a+b" => true,
            "a+t" => true,
        ],
        "write" => [
            "w" => true,
            "w+" => true,
            "rw" => true,
            "r+" => true,
            "x+" => true,
            "c+" => true,
            "wb" => true,
            "w+b" => true,
            "r+b" => true,
            "x+b" => true,
            "c+b" => true,
            "w+t" => true,
            "r+t" => true,
            "x+t" => true,
            "c+t" => true,
            "a" => true,
            "a+" => true,
            "ab" => true,
            "a+b" => true,
            "at" => true,
            "a+t" => true,
        ],
    ];

    public function __construct(mixed $stream)
    {
        if (is_string($stream)) {
            set_error_handler(function (int $errno, string $errstr): void {
                throw new InvalidArgumentException(
                    "Unable to open stream: $errstr"
                );
            });

            try {
                $this->resource = fopen($stream, "r");
            } finally {
                restore_error_handler();
            }

            $this->uri = $stream;
        } elseif (is_resource($stream)) {
            $this->resource = $stream;
        } else {
            throw new InvalidArgumentException(
                "Stream must be a resource or a string"
            );
        }

        $meta = stream_get_meta_data($this->resource);
        $this->seekable = $meta["seekable"] ?? false;
        $this->readable = isset(self::$readWriteHash["read"][$meta["mode"]]);
        $this->writable = isset(self::$readWriteHash["write"][$meta["mode"]]);
        $this->uri = $this->uri ?? ($meta["uri"] ?? null);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (Throwable) {
            return "";
        }
    }

    public function close(): void
    {
        if (isset($this->resource)) {
            if (is_resource($this->resource)) {
                fclose($this->resource);
            }
            $this->detach();
        }
    }

    public function detach(): mixed
    {
        if (!isset($this->resource)) {
            return null;
        }

        $result = $this->resource;
        unset($this->resource);
        $this->size = null;
        $this->uri = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->resource)) {
            return null;
        }

        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->resource);
        if (isset($stats["size"])) {
            $this->size = $stats["size"];
            return $this->size;
        }

        return null;
    }

    public function tell(): int
    {
        if (!isset($this->resource)) {
            throw new RuntimeException("Stream is detached");
        }

        $result = ftell($this->resource);

        if ($result === false) {
            throw new RuntimeException("Unable to determine stream position");
        }

        return $result;
    }

    public function eof(): bool
    {
        if (!isset($this->resource)) {
            throw new RuntimeException("Stream is detached");
        }

        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!isset($this->resource)) {
            throw new RuntimeException("Stream is detached");
        }

        if (!$this->seekable) {
            throw new RuntimeException("Stream is not seekable");
        }

        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new RuntimeException("Unable to seek to stream position");
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write(string $string): int
    {
        if (!isset($this->resource)) {
            throw new RuntimeException("Stream is detached");
        }

        if (!$this->writable) {
            throw new RuntimeException("Cannot write to a non-writable stream");
        }

        $this->size = null;
        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new RuntimeException("Unable to write to stream");
        }

        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read(int $length): string
    {
        if (!isset($this->resource)) {
            throw new RuntimeException("Stream is detached");
        }

        if (!$this->readable) {
            throw new RuntimeException("Cannot read from non-readable stream");
        }

        if ($length < 0) {
            throw new RuntimeException("Length parameter cannot be negative");
        }

        if ($length === 0) {
            return "";
        }

        $string = fread($this->resource, $length);

        if ($string === false) {
            throw new RuntimeException("Unable to read from stream");
        }

        return $string;
    }

    public function getContents(): string
    {
        if (!isset($this->resource)) {
            throw new RuntimeException("Stream is detached");
        }

        $contents = stream_get_contents($this->resource);

        if ($contents === false) {
            throw new RuntimeException("Unable to read stream contents");
        }

        return $contents;
    }

    public function getMetadata(?string $key = null): mixed
    {
        if (!isset($this->resource)) {
            return $key ? null : [];
        }

        $meta = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    /**
     * Create a new stream from a string.
     */
    public static function create(string $content = ""): self
    {
        $resource = fopen("php://temp", "r+");
        if ($content !== "") {
            fwrite($resource, $content);
            fseek($resource, 0);
        }
        return new self($resource);
    }

    /**
     * Create a new stream from a file.
     */
    public static function createFromFile(
        string $filename,
        string $mode = "r"
    ): self {
        return new self($filename);
    }

    /**
     * Create a new stream from a resource.
     */
    public static function createFromResource(mixed $resource): self
    {
        return new self($resource);
    }
}
