<?php

declare(strict_types=1);

namespace vosaka\http;

use Psr\Http\Message\RequestInterface;
use vosaka\foroutines\Async;
use vosaka\http\client\HttpClient;
use vosaka\http\message\Request;
use vosaka\http\message\Response;
use vosaka\http\message\ServerRequest;
use vosaka\http\message\Stream;
use vosaka\http\message\Uri;
use vosaka\http\router\Router;
use vosaka\http\server\HttpServer;
use vosaka\http\server\ServerConfig;

/**
 * Browzr — Main facade for the VOsaka HTTP library (foroutines edition).
 *
 * Client methods return Async — call ->wait() to await the result inside
 * a fiber, or pass the Async to concurrent patterns:
 *
 *   // Sequential
 *   $res = Browzr::get('https://api.example.com/users')->wait();
 *
 *   // Concurrent — both requests run in parallel
 *   $a1 = Browzr::get('https://api.example.com/users');
 *   $a2 = Browzr::get('https://api.example.com/posts');
 *   [$users, $posts] = [$a1->wait(), $a2->wait()];
 *
 * Server:
 *   main(function () {
 *       Browzr::server($router)->withDebugMode()->serve('0.0.0.0:8080');
 *   });
 */
final class Browzr
{
    private static ?HttpClient $defaultClient = null;

    // ── Client factory ───────────────────────────────────────────────────────

    public static function client(array $options = []): HttpClient
    {
        return new HttpClient($options);
    }

    public static function getDefaultClient(): HttpClient
    {
        return self::$defaultClient ??= new HttpClient();
    }

    public static function setDefaultClient(HttpClient $client): void
    {
        self::$defaultClient = $client;
    }

    public static function resetDefaultClient(): void
    {
        self::$defaultClient = null;
    }

    // ── HTTP client methods (return Async<Response>) ─────────────────────────

    public static function get(
        string $url,
        array  $headers = [],
        array  $options = [],
    ): Async {
        return self::getDefaultClient()->get($url, $headers, $options);
    }

    public static function post(
        string $url,
        mixed  $body    = null,
        array  $headers = [],
        array  $options = [],
    ): Async {
        return self::getDefaultClient()->post($url, $body, $headers, $options);
    }

    public static function put(
        string $url,
        mixed  $body    = null,
        array  $headers = [],
        array  $options = [],
    ): Async {
        return self::getDefaultClient()->put($url, $body, $headers, $options);
    }

    public static function patch(
        string $url,
        mixed  $body    = null,
        array  $headers = [],
        array  $options = [],
    ): Async {
        return self::getDefaultClient()->patch($url, $body, $headers, $options);
    }

    public static function delete(
        string $url,
        array  $headers = [],
        array  $options = [],
    ): Async {
        return self::getDefaultClient()->delete($url, $headers, $options);
    }

    public static function head(
        string $url,
        array  $headers = [],
        array  $options = [],
    ): Async {
        return self::getDefaultClient()->head($url, $headers, $options);
    }

    public static function send(
        RequestInterface $request,
        array            $options = [],
    ): Async {
        return self::getDefaultClient()->send($request, $options);
    }

    // ── Server factory ───────────────────────────────────────────────────────

    public static function server(
        Router        $router,
        ?ServerConfig $config = null,
    ): HttpServer {
        return new HttpServer($router, $config);
    }

    // ── PSR-7 message factories ──────────────────────────────────────────────

    public static function request(
        string $method,
        string $uri,
        array  $headers         = [],
        mixed  $body            = null,
        string $protocolVersion = '1.1',
    ): Request {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    public static function response(
        int    $statusCode      = 200,
        array  $headers         = [],
        mixed  $body            = null,
        string $protocolVersion = '1.1',
        string $reasonPhrase    = '',
    ): Response {
        return new Response($statusCode, $headers, $body, $protocolVersion, $reasonPhrase);
    }

    public static function serverRequest(
        string $method,
        string $uri,
        array  $headers         = [],
        mixed  $body            = null,
        string $protocolVersion = '1.1',
        array  $serverParams    = [],
    ): ServerRequest {
        return new ServerRequest($method, $uri, $headers, $body, $protocolVersion, $serverParams);
    }

    public static function serverRequestFromGlobals(): ServerRequest
    {
        return ServerRequest::fromGlobals();
    }

    public static function uri(string $uri): Uri
    {
        return new Uri($uri);
    }

    public static function stream(string $content = ''): Stream
    {
        return Stream::create($content);
    }

    public static function streamFromFile(string $filename, string $mode = 'r'): Stream
    {
        return Stream::createFromFile($filename, $mode);
    }

    public static function streamFromResource(mixed $resource): Stream
    {
        return Stream::createFromResource($resource);
    }

    // ── Response shortcuts ───────────────────────────────────────────────────

    public static function json(mixed $data, int $statusCode = 200, array $headers = []): Response
    {
        return Response::json($data, $statusCode, $headers);
    }

    public static function html(string $html, int $statusCode = 200, array $headers = []): Response
    {
        return Response::html($html, $statusCode, $headers);
    }

    public static function text(string $text, int $statusCode = 200, array $headers = []): Response
    {
        return Response::text($text, $statusCode, $headers);
    }

    public static function redirect(string $url, int $statusCode = 302): Response
    {
        return Response::redirect($url, $statusCode);
    }

    public static function notFound(string $message = 'Not Found'): Response
    {
        return new Response(404, [], Stream::create($message));
    }

    public static function serverError(string $message = 'Internal Server Error'): Response
    {
        return new Response(500, [], Stream::create($message));
    }

    public static function badRequest(string $message = 'Bad Request'): Response
    {
        return new Response(400, [], Stream::create($message));
    }

    public static function unauthorized(string $message = 'Unauthorized'): Response
    {
        return new Response(401, [], Stream::create($message));
    }

    public static function forbidden(string $message = 'Forbidden'): Response
    {
        return new Response(403, [], Stream::create($message));
    }

    public static function unprocessableEntity(mixed $errors, string $message = 'Unprocessable Entity'): Response
    {
        if (is_array($errors) || is_object($errors)) {
            return Response::json(['message' => $message, 'errors' => $errors], 422);
        }
        return new Response(422, [], Stream::create($message));
    }

    public static function tooManyRequests(string $message = 'Too Many Requests', ?int $retryAfter = null): Response
    {
        $headers = $retryAfter !== null ? ['Retry-After' => (string) $retryAfter] : [];
        return new Response(429, $headers, Stream::create($message));
    }
}
