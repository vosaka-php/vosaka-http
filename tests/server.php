<?php
require "../vendor/autoload.php";

use Psr\Http\Message\ServerRequestInterface;
use venndev\vosaka\VOsaka;
use vosaka\http\message\Response;
use vosaka\http\server\Router;
use vosaka\http\middleware\CorsMiddleware;
use vosaka\http\server\HttpServer;

echo "=== HTTP Server Examples ===\n";

$router = Router::new()->get(
    "/",
    function (ServerRequestInterface $req) {
        return Response::json([
            "message" => "Hello world!",
        ]);
    },
    "index" // Named route
);

$server = HttpServer::new($router)
    ->withDebugMode(false)
    ->layer(CorsMiddleware::permissive());

echo "Starting server on 0.0.0.0:8888...\n";

VOsaka::spawn($server->serve("0.0.0.0:8888")->unwrap());
VOsaka::run();
