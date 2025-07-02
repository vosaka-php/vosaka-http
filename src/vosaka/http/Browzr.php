<?php

declare(strict_types=1);

namespace vosaka\http;

use venndev\vosaka\core\Result;
use vosaka\http\client\HttpClient;
use vosaka\http\server\HttpServer;
use vosaka\http\message\Request;
use vosaka\http\message\Response;
use vosaka\http\message\ServerRequest;
use vosaka\http\message\Stream;
use vosaka\http\message\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use vosaka\http\router\Router;
use vosaka\http\server\ServerConfig;

/**
 * VOsaka HTTP Library - Main Facade
 *
 * Browzr serves as the main entry point and facade for the VOsaka HTTP library.
 * It provides convenient static methods for common HTTP operations including
 * client requests, server creation, and message manipulation.
 */
final class Browzr
{
    private static ?HttpClient $defaultClient = null;

    /**
     * Create a new HTTP client with optional configuration.
     */
    public static function client(array $options = []): HttpClient
    {
        return new HttpClient($options);
    }

    public static function server(Router $router, ?ServerConfig $config = null)
    {
        return new HttpServer($router, $config);
    }

    /**
     * Get the default HTTP client instance (singleton).
     */
    public static function getDefaultClient(): HttpClient
    {
        if (self::$defaultClient === null) {
            self::$defaultClient = new HttpClient();
        }
        return self::$defaultClient;
    }

    /**
     * Send a GET request asynchronously.
     */
    public static function get(
        string $url,
        array $headers = [],
        array $options = []
    ): Result {
        return self::getDefaultClient()->get($url, $headers, $options);
    }

    /**
     * Send a POST request asynchronously.
     */
    public static function post(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = []
    ): Result {
        return self::getDefaultClient()->post($url, $body, $headers, $options);
    }

    /**
     * Send a PUT request asynchronously.
     */
    public static function put(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = []
    ): Result {
        return self::getDefaultClient()->put($url, $body, $headers, $options);
    }

    /**
     * Send a DELETE request asynchronously.
     */
    public static function delete(
        string $url,
        array $headers = [],
        array $options = []
    ): Result {
        return self::getDefaultClient()->delete($url, $headers, $options);
    }

    /**
     * Send a PATCH request asynchronously.
     */
    public static function patch(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = []
    ): Result {
        return self::getDefaultClient()->patch($url, $body, $headers, $options);
    }

    /**
     * Send a HEAD request asynchronously.
     */
    public static function head(
        string $url,
        array $headers = [],
        array $options = []
    ): Result {
        return self::getDefaultClient()->head($url, $headers, $options);
    }

    /**
     * Send an OPTIONS request asynchronously.
     */
    public static function options(
        string $url,
        array $headers = [],
        array $options = []
    ): Result {
        return self::getDefaultClient()->options($url, $headers, $options);
    }

    /**
     * Send a custom HTTP request asynchronously.
     */
    public static function send(
        RequestInterface $request,
        array $options = []
    ): Result {
        return self::getDefaultClient()->send($request, $options);
    }

    // ===== Factory Methods =====

    /**
     * Create a new HTTP request.
     */
    public static function request(
        string $method,
        string $uri,
        array $headers = [],
        mixed $body = null,
        string $protocolVersion = "1.1"
    ): Request {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * Create a new HTTP response.
     */
    public static function response(
        int $statusCode = 200,
        array $headers = [],
        mixed $body = null,
        string $protocolVersion = "1.1",
        string $reasonPhrase = ""
    ): Response {
        return new Response(
            $statusCode,
            $headers,
            $body,
            $protocolVersion,
            $reasonPhrase
        );
    }

    /**
     * Create a new server request.
     */
    public static function serverRequest(
        string $method,
        string $uri,
        array $headers = [],
        mixed $body = null,
        string $protocolVersion = "1.1",
        array $serverParams = []
    ): ServerRequest {
        return new ServerRequest(
            $method,
            $uri,
            $headers,
            $body,
            $protocolVersion,
            $serverParams
        );
    }

    /**
     * Create a server request from global variables.
     */
    public static function serverRequestFromGlobals(): ServerRequest
    {
        return ServerRequest::fromGlobals();
    }

    /**
     * Create a new URI.
     */
    public static function uri(string $uri): Uri
    {
        return new Uri($uri);
    }

    /**
     * Create a new stream.
     */
    public static function stream(string $content = ""): Stream
    {
        return Stream::create($content);
    }

    /**
     * Create a stream from a file.
     */
    public static function streamFromFile(
        string $filename,
        string $mode = "r"
    ): Stream {
        return Stream::createFromFile($filename, $mode);
    }

    /**
     * Create a stream from a resource.
     */
    public static function streamFromResource(mixed $resource): Stream
    {
        return Stream::createFromResource($resource);
    }

    /**
     * Create a JSON response.
     */
    public static function json(
        mixed $data,
        int $statusCode = 200,
        array $headers = []
    ): Response {
        return Response::json($data, $statusCode, $headers);
    }

    /**
     * Create an HTML response.
     */
    public static function html(
        string $html,
        int $statusCode = 200,
        array $headers = []
    ): Response {
        return Response::html($html, $statusCode, $headers);
    }

    /**
     * Create a plain text response.
     */
    public static function text(
        string $text,
        int $statusCode = 200,
        array $headers = []
    ): Response {
        return Response::text($text, $statusCode, $headers);
    }

    /**
     * Create a redirect response.
     */
    public static function redirect(
        string $url,
        int $statusCode = 302
    ): Response {
        return Response::redirect($url, $statusCode);
    }

    /**
     * Create a 404 Not Found response.
     */
    public static function notFound(string $message = "Not Found"): Response
    {
        return new Response(404, [], $message);
    }

    /**
     * Create a 500 Internal Server Error response.
     */
    public static function serverError(
        string $message = "Internal Server Error"
    ): Response {
        return new Response(500, [], $message);
    }

    /**
     * Create a 400 Bad Request response.
     */
    public static function badRequest(string $message = "Bad Request"): Response
    {
        return new Response(400, [], $message);
    }

    /**
     * Create a 401 Unauthorized response.
     */
    public static function unauthorized(
        string $message = "Unauthorized"
    ): Response {
        return new Response(401, [], $message);
    }

    /**
     * Create a 403 Forbidden response.
     */
    public static function forbidden(string $message = "Forbidden"): Response
    {
        return new Response(403, [], $message);
    }

    /**
     * Create a 422 Unprocessable Entity response.
     */
    public static function unprocessableEntity(
        mixed $errors,
        string $message = "Unprocessable Entity"
    ): Response {
        if (is_array($errors) || is_object($errors)) {
            return self::json(
                ["message" => $message, "errors" => $errors],
                422
            );
        }
        return new Response(422, [], $message);
    }

    /**
     * Create a 429 Too Many Requests response.
     */
    public static function tooManyRequests(
        string $message = "Too Many Requests",
        ?int $retryAfter = null
    ): Response {
        $headers = [];
        if ($retryAfter !== null) {
            $headers["Retry-After"] = (string) $retryAfter;
        }
        return new Response(429, $headers, $message);
    }

    /**
     * Set the default HTTP client.
     */
    public static function setDefaultClient(HttpClient $client): void
    {
        self::$defaultClient = $client;
    }

    /**
     * Reset the default HTTP client to null.
     */
    public static function resetDefaultClient(): void
    {
        self::$defaultClient = null;
    }
}
