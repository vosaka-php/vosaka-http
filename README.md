# VOsaka HTTP

Asynchronous HTTP library for PHP with PSR-7 messages, router, middleware, HTTP client, and HTTP server.

## Features

- Async HTTP client (`Browzr`)
- Async HTTP server (`HttpServer`)
- PSR-7 message implementation
- Router with path params
- Middleware stack (CORS, favicon, custom middleware)
- Strict types, PHP 8+

## Installation

```bash
composer require venndev/vosaka-http
```

## HTTP Server (Current Flow)

`HttpServer` now runs this pipeline:

`socket -> read -> string buffer -> HTTP parser -> handler -> response builder -> string -> socket write`

This is implemented in:

- [src/vosaka/http/server/HttpServer.php](src/vosaka/http/server/HttpServer.php)

## Quick Start (Server)

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use vosaka\http\message\Response;
use vosaka\http\middleware\CorsMiddleware;
use vosaka\http\router\Router;
use vosaka\http\server\HttpServer;
use vosaka\foroutines\AsyncMain;

#[AsyncMain]
function main() {
    $router = Router::new()
        ->get('/health', function (ServerRequestInterface $req) {
            return Response::json([
                'status' => 'ok',
                'time' => date('c'),
            ]);
        });

    $server = HttpServer::new($router)
        ->withDebugMode(true)
        ->layer(CorsMiddleware::permissive());

    $server->serve('0.0.0.0:8888');
}
```

## Quick Start (Client)

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use vosaka\http\Browzr;

$response = Browzr::get('https://httpbin.org/get')->await();
echo $response->getStatusCode() . PHP_EOL;
```

## Local Test

```bash
php tests/server.php
```
