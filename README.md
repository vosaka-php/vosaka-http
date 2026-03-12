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
PHP  wrk -t12 -c10000 -d30s http://127.0.0.1:8888
Running 30s test @ http://127.0.0.1:8888
  12 threads and 10000 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency   416.48ms   38.54ms 742.26ms   95.75%
    Req/Sec     2.00k   719.10     4.31k    68.44%
  714611 requests in 30.08s, 283.51MB read
Requests/sec:  23758.13
Transfer/sec:      9.43MB

 NODEJS  wrk -t12 -c10000 -d30s http://127.0.0.1:8888
Running 30s test @ http://127.0.0.1:8888
  12 threads and 10000 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    39.46ms   14.92ms   1.98s    99.86%
    Req/Sec     2.87k     1.73k    7.65k    60.89%
  1027131 requests in 30.08s, 673.93MB read
  Socket errors: connect 0, read 509, write 0, timeout 719
Requests/sec:  34147.09
Transfer/sec:     22.40MB
```
