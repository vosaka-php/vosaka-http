# VOsaka HTTP Library

A powerful asynchronous HTTP library for PHP built on top of the VOsaka async runtime system. This library provides both HTTP client and server capabilities with full PSR-7 compatibility.

## Features

- **Asynchronous HTTP Client**: Make concurrent HTTP requests without blocking
- **Asynchronous HTTP Server**: Handle multiple connections concurrently
- **PSR-7 Compatible**: Full implementation of PSR-7 HTTP message interfaces
- **Advanced Routing**: Parameter extraction, route groups, and middleware support
- **Built-in Middleware**: CORS, rate limiting, and custom middleware support
- **Stream Support**: Efficient handling of large payloads
- **SSL/TLS Support**: Secure connections for both client and server
- **Modern PHP**: Uses PHP 8+ features with strict typing

## Installation

```bash
composer require venndev/vosaka-http
```

## Quick Start

### HTTP Client

```php
<?php

require_once 'vendor/autoload.php';

use venndev\vosaka\VOsaka;
use vosaka\http\Browzr;

// Simple async function
function makeRequests(): Generator
{
    // GET request
    $response = yield from Browzr::get('https://api.example.com/users')->unwrap();
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Body: " . $response->getBody()->getContents() . "\n";

    // POST request with JSON
    $data = ['name' => 'John', 'email' => 'john@example.com'];
    $response = yield from Browzr::post(
        'https://api.example.com/users',
        $data,
        ['Content-Type' => 'application/json']
    )->unwrap();

    return $response;
}

// Run with VOsaka
VOsaka::spawn(makeRequests());
VOsaka::run();
```

### HTTP Server

```php
<?php
require "../vendor/autoload.php";

use Psr\Http\Message\ServerRequestInterface;
use venndev\vosaka\VOsaka;
use vosaka\http\message\Response;
use vosaka\http\server\Router;
use vosaka\http\middleware\CorsMiddleware;
use vosaka\http\server\HttpServer;

echo "=== HTTP Server Examples ===\n";

$router = Router::new()
    ->get(
        "/users/{id:\d+}",
        function (ServerRequestInterface $req) {
            return Response::json([
                "user_id" => $req->getAttribute("id"),
                "message" => "User found with ID: " . $req->getAttribute("id"),
            ]);
        },
        "user.show" // Named route
    )
    ->get(
        "/posts/{slug}",
        function (ServerRequestInterface $req) {
            return Response::json([
                "post_slug" => $req->getAttribute("slug"),
                "message" =>
                    "Post found with slug: " . $req->getAttribute("slug"),
            ]);
        },
        "post.show"
    )
    ->get(
        "/",
        function (ServerRequestInterface $req) {
            return Response::json([
                "message" => "Welcome to VOsaka HTTP API",
                "version" => "2.0",
                "routes" => [
                    "GET /" => "This welcome message",
                    "GET /users/{id}" => "Get user by ID (numeric only)",
                    "GET /posts/{slug}" => "Get post by slug",
                    "GET /health" => "Health check endpoint",
                ],
                "examples" => ["/users/123", "/posts/hello-world", "/health"],
            ]);
        },
        "home"
    )
    ->get(
        "/health",
        function (ServerRequestInterface $req) {
            return Response::json([
                "status" => "healthy",
                "timestamp" => date("c"),
                "uptime" => "Server is running",
            ]);
        },
        "health"
    );

$server = HttpServer::new($router)
    ->withDebugMode(true)
    ->layer(CorsMiddleware::permissive());

echo "Starting server on 0.0.0.0:8888...\n";

VOsaka::spawn($server->serve("0.0.0.0:8888")->unwrap());
VOsaka::run();

```

## Concurrent Requests

```php
function concurrentRequests(): Generator
{
    // Make multiple requests concurrently
    $responses = yield from VOsaka::join(
        Browzr::get('https://api1.example.com/data'),
        Browzr::get('https://api2.example.com/data'),
        Browzr::get('https://api3.example.com/data')
    )->unwrap();

    foreach ($responses as $i => $response) {
        echo "Response $i: " . $response->getStatusCode() . "\n";
    }
}
```

## Advanced Features

### Custom HTTP Client

```php
$client = Browzr::client([
    'timeout' => 30,
    'user_agent' => 'MyApp/1.0',
    'headers' => [
        'Authorization' => 'Bearer ' . $token
    ]
]);

$response = yield from $client->get('https://api.example.com/protected')->unwrap();
```
