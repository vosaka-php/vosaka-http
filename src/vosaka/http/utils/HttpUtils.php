<?php

declare(strict_types=1);

namespace vosaka\http\utils;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use vosaka\http\message\Response;
use vosaka\http\message\Stream;

/**
 * HTTP utility functions for common tasks.
 *
 * This class provides static utility methods for working with HTTP
 * messages, headers, content types, and other common HTTP operations.
 */
final class HttpUtils
{
    /**
     * @var array<string, string>
     */
    private static array $mimeTypes = [
        'html' => 'text/html',
        'htm' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'rss' => 'application/rss+xml',
        'atom' => 'application/atom+xml',
        'txt' => 'text/plain',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'ico' => 'image/x-icon',
        'svg' => 'image/svg+xml',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'mp4' => 'video/mp4',
        'avi' => 'video/x-msvideo',
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    /**
     * @var array<int, string>
     */
    private static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
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

    /**
     * Get MIME type for a file extension.
     */
    public static function getMimeType(string $extension): string
    {
        $extension = strtolower(ltrim($extension, '.'));
        return self::$mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Get MIME type from a file path.
     */
    public static function getMimeTypeFromPath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return self::getMimeType($extension);
    }

    /**
     * Get status text for HTTP status code.
     */
    public static function getStatusText(int $statusCode): string
    {
        return self::$statusTexts[$statusCode] ?? 'Unknown Status';
    }

    /**
     * Check if HTTP status code is informational (1xx).
     */
    public static function isInformational(int $statusCode): bool
    {
        return $statusCode >= 100 && $statusCode < 200;
    }

    /**
     * Check if HTTP status code is successful (2xx).
     */
    public static function isSuccessful(int $statusCode): bool
    {
        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Check if HTTP status code is a redirection (3xx).
     */
    public static function isRedirection(int $statusCode): bool
    {
        return $statusCode >= 300 && $statusCode < 400;
    }

    /**
     * Check if HTTP status code is a client error (4xx).
     */
    public static function isClientError(int $statusCode): bool
    {
        return $statusCode >= 400 && $statusCode < 500;
    }

    /**
     * Check if HTTP status code is a server error (5xx).
     */
    public static function isServerError(int $statusCode): bool
    {
        return $statusCode >= 500 && $statusCode < 600;
    }

    /**
     * Parse HTTP Accept header and return accepted types with quality scores.
     */
    public static function parseAcceptHeader(string $accept): array
    {
        $types = [];
        $parts = explode(',', $accept);

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $segments = explode(';', $part);
            $type = trim($segments[0]);
            $quality = 1.0;

            // Parse quality value
            for ($i = 1; $i < count($segments); $i++) {
                $param = trim($segments[$i]);
                if (str_starts_with($param, 'q=')) {
                    $quality = (float) substr($param, 2);
                    break;
                }
            }

            $types[] = [
                'type' => $type,
                'quality' => $quality,
            ];
        }

        // Sort by quality score (descending)
        usort($types, fn($a, $b) => $b['quality'] <=> $a['quality']);

        return $types;
    }

    /**
     * Find the best matching content type based on Accept header.
     */
    public static function negotiateContentType(string $accept, array $availableTypes): ?string
    {
        $acceptedTypes = self::parseAcceptHeader($accept);

        foreach ($acceptedTypes as $acceptedType) {
            $type = $acceptedType['type'];

            // Exact match
            if (in_array($type, $availableTypes, true)) {
                return $type;
            }

            // Wildcard matching
            if (str_contains($type, '*')) {
                foreach ($availableTypes as $availableType) {
                    if (self::matchMediaType($type, $availableType)) {
                        return $availableType;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Check if a media type matches a pattern (with wildcards).
     */
    public static function matchMediaType(string $pattern, string $type): bool
    {
        $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
        return preg_match("/^{$pattern}$/i", $type) === 1;
    }

    /**
     * Parse cookies from Cookie header.
     */
    public static function parseCookies(string $cookieHeader): array
    {
        $cookies = [];
        $parts = explode(';', $cookieHeader);

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if (str_contains($part, '=')) {
                [$name, $value] = explode('=', $part, 2);
                $cookies[trim($name)] = trim($value);
            }
        }

        return $cookies;
    }

    /**
     * Format cookies for Set-Cookie header.
     */
    public static function formatSetCookie(
        string $name,
        string $value,
        array $options = []
    ): string {
        $cookie = $name . '=' . $value;

        if (isset($options['expires'])) {
            $cookie .= '; Expires=' . gmdate('D, d M Y H:i:s T', $options['expires']);
        }

        if (isset($options['max_age'])) {
            $cookie .= '; Max-Age=' . $options['max_age'];
        }

        if (isset($options['domain'])) {
            $cookie .= '; Domain=' . $options['domain'];
        }

        if (isset($options['path'])) {
            $cookie .= '; Path=' . $options['path'];
        }

        if (!empty($options['secure'])) {
            $cookie .= '; Secure';
        }

        if (!empty($options['httponly'])) {
            $cookie .= '; HttpOnly';
        }

        if (isset($options['samesite'])) {
            $cookie .= '; SameSite=' . $options['samesite'];
        }

        return $cookie;
    }

    /**
     * Parse query string into an array.
     */
    public static function parseQuery(string $query): array
    {
        $params = [];
        parse_str($query, $params);
        return $params;
    }

    /**
     * Build query string from array.
     */
    public static function buildQuery(array $params): string
    {
        return http_build_query($params);
    }

    /**
     * Parse basic authentication header.
     */
    public static function parseBasicAuth(string $authHeader): ?array
    {
        if (!str_starts_with($authHeader, 'Basic ')) {
            return null;
        }

        $credentials = base64_decode(substr($authHeader, 6));
        if ($credentials === false) {
            return null;
        }

        $parts = explode(':', $credentials, 2);
        if (count($parts) !== 2) {
            return null;
        }

        return [
            'username' => $parts[0],
            'password' => $parts[1],
        ];
    }

    /**
     * Parse bearer token from Authorization header.
     */
    public static function parseBearerToken(string $authHeader): ?string
    {
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return trim(substr($authHeader, 7));
    }

    /**
     * Check if request is an AJAX request.
     */
    public static function isAjax(ServerRequestInterface $request): bool
    {
        return strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }

    /**
     * Check if request is HTTPS.
     */
    public static function isHttps(ServerRequestInterface $request): bool
    {
        $serverParams = $request->getServerParams();

        return (
            isset($serverParams['HTTPS']) && $serverParams['HTTPS'] !== 'off'
        ) || (
            isset($serverParams['SERVER_PORT']) && $serverParams['SERVER_PORT'] == 443
        ) || (
            strtolower($request->getHeaderLine('X-Forwarded-Proto')) === 'https'
        );
    }

    /**
     * Get client IP address from request.
     */
    public static function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        // Check headers in order of preference
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx
            'HTTP_X_FORWARDED_FOR',      // Load balancers
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($serverParams[$header])) {
                $ip = $serverParams[$header];

                // Handle comma-separated IPs
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $serverParams['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Create a file download response.
     */
    public static function download(
        string $filePath,
        ?string $filename = null,
        array $headers = []
    ): ResponseInterface {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: $filePath");
        }

        $filename = $filename ?? basename($filePath);
        $mimeType = self::getMimeTypeFromPath($filePath);
        $fileSize = filesize($filePath);

        $defaultHeaders = [
            'Content-Type' => $mimeType,
            'Content-Length' => (string) $fileSize,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $headers = array_merge($defaultHeaders, $headers);
        $stream = Stream::createFromFile($filePath, 'r');

        return new Response(200, $headers, $stream);
    }

    /**
     * Create a server-sent events response.
     */
    public static function serverSentEvents(): ResponseInterface
    {
        return new Response(200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Cache-Control',
        ]);
    }

    /**
     * Format data for server-sent events.
     */
    public static function formatSseData(
        mixed $data,
        ?string $event = null,
        ?string $id = null,
        ?int $retry = null
    ): string {
        $output = '';

        if ($id !== null) {
            $output .= "id: $id\n";
        }

        if ($event !== null) {
            $output .= "event: $event\n";
        }

        if ($retry !== null) {
            $output .= "retry: $retry\n";
        }

        $dataString = is_string($data) ? $data : json_encode($data);
        $lines = explode("\n", $dataString);

        foreach ($lines as $line) {
            $output .= "data: $line\n";
        }

        $output .= "\n";

        return $output;
    }

    /**
     * Validate HTTP method.
     */
    public static function isValidMethod(string $method): bool
    {
        $validMethods = [
            'GET', 'POST', 'PUT', 'DELETE', 'PATCH',
            'HEAD', 'OPTIONS', 'TRACE', 'CONNECT'
        ];

        return in_array(strtoupper($method), $validMethods, true);
    }

    /**
     * Sanitize filename for safe usage.
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts
        $filename = str_replace(['../', '.\\', '..\\'], '', $filename);

        // Remove or replace invalid characters
        $filename = preg_replace('/[<>:"|?*]/', '', $filename);

        // Remove control characters
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);

        // Trim whitespace and dots
        $filename = trim($filename, " \t\n\r\0\x0B.");

        // Ensure filename is not empty
        return $filename === '' ? 'unnamed' : $filename;
    }
}
