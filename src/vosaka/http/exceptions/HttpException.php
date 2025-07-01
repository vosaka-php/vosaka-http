<?php

declare(strict_types=1);

namespace vosaka\http\exceptions;

use Throwable;

/**
 * HTTP Exception
 */
class HttpException extends \Exception
{
    public function __construct(
        string $message,
        private int $statusCode = 500,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
