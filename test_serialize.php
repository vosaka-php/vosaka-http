<?php
require 'vendor/autoload.php';
use vosaka\http\router\Router;
use vosaka\http\server\HttpServer;

$router = Router::new()->get('/', function() {});
$server = HttpServer::new($router);

$fn = static function() use ($router) { echo "OK"; };

try {
    $s = serialize(new \Laravel\SerializableClosure\SerializableClosure($fn));
    echo "Serialized size: " . strlen($s) . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
