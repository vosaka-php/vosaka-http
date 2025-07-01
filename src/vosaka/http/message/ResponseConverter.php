<?php

declare(strict_types=1);

namespace vosaka\http\message;

use Psr\Http\Message\ResponseInterface;

final class ResponseConverter
{
    public function convert(mixed $result): ResponseInterface
    {
        return match (true) {
            $result instanceof ResponseInterface => $result,
            is_string($result) => Response::text($result),
            is_array($result) || is_object($result) => Response::json($result),
            is_null($result) => new Response(204), // No Content
            is_bool($result) => Response::text($result ? "true" : "false"),
            is_numeric($result) => Response::text((string) $result),
            default => new Response(200, [], Stream::create((string) $result)),
        };
    }
}
