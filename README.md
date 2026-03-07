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

# Benchmark
```yml
PHP ➜  ~ wrk -t12 -c1000 -d30s http://192.168.2.8:8888
Running 30s test @ http://192.168.2.8:8888
  12 threads and 1000 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    74.85ms   13.03ms 248.39ms   82.83%
    Req/Sec     1.11k   159.39     1.64k    76.43%
  397881 requests in 30.10s, 162.02MB read
Requests/sec:  13218.93
Transfer/sec:      5.38MB


NODEJS ➜  ~ wrk -t12 -c1000 -d30s http://192.168.2.8:8888
Running 30s test @ http://192.168.2.8:8888
  12 threads and 1000 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    85.24ms   28.84ms 870.27ms   94.57%
    Req/Sec     0.98k   186.48     1.69k    74.64%
  352045 requests in 30.10s, 230.99MB read
  Socket errors: connect 0, read 0, write 1558, timeout 0
Requests/sec:  11695.57
Transfer/sec:      7.67MB
```
