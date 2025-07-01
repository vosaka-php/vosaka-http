<?php

declare(strict_types=1);

namespace vosaka\http\middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use vosaka\http\message\Response;

/**
 * Rate limiting middleware for request throttling.
 *
 * This middleware implements token bucket algorithm to limit the number
 * of requests per client within a specified time window. It helps protect
 * against abuse and ensures fair resource usage.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    private array $buckets = [];
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'requests' => 100,        // Max requests per window
            'window' => 3600,         // Time window in seconds (1 hour)
            'key_generator' => null,  // Custom key generator function
            'skip_successful' => false, // Don't count successful requests
            'headers' => true,        // Include rate limit headers in response
            'message' => 'Rate limit exceeded',
            'store' => 'memory',      // Storage backend (memory, redis, etc.)
        ], $options);
    }

    public function process(ServerRequestInterface $request, callable $next): ?ResponseInterface
    {
        $key = $this->generateKey($request);
        $bucket = $this->getBucket($key);

        // Check if request is allowed
        if (!$this->isAllowed($bucket)) {
            return $this->createRateLimitResponse($bucket);
        }

        // Process the request
        $response = $next($request);

        // Update bucket (consume token)
        if (!$this->options['skip_successful'] || !$this->isSuccessfulResponse($response)) {
            $this->consumeToken($bucket);
        }

        // Add rate limit headers if enabled
        if ($this->options['headers']) {
            $response = $this->addRateLimitHeaders($response, $bucket);
        }

        return $response;
    }

    private function generateKey(ServerRequestInterface $request): string
    {
        if ($this->options['key_generator'] && is_callable($this->options['key_generator'])) {
            return call_user_func($this->options['key_generator'], $request);
        }

        // Default: use client IP address
        $clientIp = $this->getClientIp($request);
        return 'rate_limit:' . $clientIp;
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        // Check for IP from various headers (in order of preference)
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

                // Handle comma-separated IPs (X-Forwarded-For)
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $serverParams['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private function getBucket(string $key): array
    {
        $now = time();

        if (!isset($this->buckets[$key])) {
            $this->buckets[$key] = [
                'tokens' => $this->options['requests'],
                'last_refill' => $now,
                'requests' => 0,
                'window_start' => $now,
            ];
        }

        $bucket = &$this->buckets[$key];

        // Refill tokens based on elapsed time
        $this->refillBucket($bucket, $now);

        return $bucket;
    }

    private function refillBucket(array &$bucket, int $now): void
    {
        $windowElapsed = $now - $bucket['window_start'];

        // Reset window if it has expired
        if ($windowElapsed >= $this->options['window']) {
            $bucket['tokens'] = $this->options['requests'];
            $bucket['requests'] = 0;
            $bucket['window_start'] = $now;
            $bucket['last_refill'] = $now;
            return;
        }

        // Calculate tokens to add based on time elapsed
        $timeElapsed = $now - $bucket['last_refill'];
        if ($timeElapsed > 0) {
            $tokensToAdd = ($timeElapsed / $this->options['window']) * $this->options['requests'];
            $bucket['tokens'] = min($this->options['requests'], $bucket['tokens'] + $tokensToAdd);
            $bucket['last_refill'] = $now;
        }
    }

    private function isAllowed(array $bucket): bool
    {
        return $bucket['tokens'] >= 1;
    }

    private function consumeToken(array &$bucket): void
    {
        if ($bucket['tokens'] >= 1) {
            $bucket['tokens']--;
            $bucket['requests']++;
        }
    }

    private function isSuccessfulResponse(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();
        return $statusCode >= 200 && $statusCode < 400;
    }

    private function createRateLimitResponse(array $bucket): ResponseInterface
    {
        $retryAfter = $this->calculateRetryAfter($bucket);

        $response = new Response(429, [
            'Content-Type' => 'application/json',
            'Retry-After' => (string) $retryAfter,
        ], json_encode([
            'error' => $this->options['message'],
            'retry_after' => $retryAfter,
        ]));

        return $this->addRateLimitHeaders($response, $bucket);
    }

    private function addRateLimitHeaders(ResponseInterface $response, array $bucket): ResponseInterface
    {
        $remaining = max(0, (int) floor($bucket['tokens']));
        $resetTime = $bucket['window_start'] + $this->options['window'];

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->options['requests'])
            ->withHeader('X-RateLimit-Remaining', (string) $remaining)
            ->withHeader('X-RateLimit-Reset', (string) $resetTime)
            ->withHeader('X-RateLimit-Window', (string) $this->options['window']);
    }

    private function calculateRetryAfter(array $bucket): int
    {
        $windowEnd = $bucket['window_start'] + $this->options['window'];
        $now = time();

        return max(1, $windowEnd - $now);
    }

    /**
     * Create rate limit middleware with lenient settings.
     */
    public static function lenient(): self
    {
        return new self([
            'requests' => 1000,
            'window' => 3600, // 1 hour
        ]);
    }

    /**
     * Create rate limit middleware with strict settings.
     */
    public static function strict(): self
    {
        return new self([
            'requests' => 60,
            'window' => 3600, // 1 hour
        ]);
    }

    /**
     * Create rate limit middleware for API endpoints.
     */
    public static function api(int $requestsPerHour = 100): self
    {
        return new self([
            'requests' => $requestsPerHour,
            'window' => 3600,
            'skip_successful' => false,
            'key_generator' => function (ServerRequestInterface $request): string {
                // Try to get API key from header or query parameter
                $apiKey = $request->getHeaderLine('X-API-Key')
                    ?: $request->getQueryParams()['api_key'] ?? null;

                if ($apiKey) {
                    return 'api_rate_limit:' . hash('sha256', $apiKey);
                }

                // Fall back to IP-based limiting
                $clientIp = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
                return 'api_rate_limit:ip:' . $clientIp;
            },
        ]);
    }

    /**
     * Clean up expired buckets to prevent memory leaks.
     */
    public function cleanup(): void
    {
        $now = time();
        $expiredKeys = [];

        foreach ($this->buckets as $key => $bucket) {
            $windowAge = $now - $bucket['window_start'];
            if ($windowAge > $this->options['window'] * 2) {
                $expiredKeys[] = $key;
            }
        }

        foreach ($expiredKeys as $key) {
            unset($this->buckets[$key]);
        }
    }
}
