<?php


declare(strict_types=1);
ini_set("memory_limit", "-1");

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use vosaka\foroutines\AsyncMain;
use vosaka\http\message\Response;
use vosaka\http\middleware\CorsMiddleware;
use vosaka\http\middleware\FaviconMiddleware;
use vosaka\http\router\Router;
use vosaka\http\server\HttpServer;


#[AsyncMain]
function main() {
	$router = Router::new()
		->get(
			'/users/{id:\d+}',
			function (ServerRequestInterface $req) {
				return Response::json([
					'user_id' => $req->getAttribute('id'),
					'message' => 'User found with ID: ' . $req->getAttribute('id'),
				]);
			},
			'user.show',
		)
		->get(
			'/posts/{slug}',
			function (ServerRequestInterface $req) {
				return Response::json([
					'post_slug' => $req->getAttribute('slug'),
					'message'   => 'Post found with slug: ' . $req->getAttribute('slug'),
				]);
			},
			'post.show',
		)
		->get(
			'/',
			function (ServerRequestInterface $req) {
				return Response::json([
					'message' => 'Welcome to VOsaka HTTP API',
					'version' => '2.0',
					'routes'  => [
						'GET /'             => 'This welcome message',
						'GET /users/{id}'   => 'Get user by ID (numeric only)',
						'GET /posts/{slug}' => 'Get post by slug',
						'GET /health'       => 'Health check endpoint',
					],
					'examples' => ['/users/123', '/posts/hello-world', '/health'],
				]);
			},
			'home',
		)
		->get(
			'/health',
			function (ServerRequestInterface $req) {
				return Response::json([
					'status'    => 'healthy',
					'timestamp' => date('c'),
					'uptime'    => 'Server is running',
				]);
			},
			'health',
		);

	$server = HttpServer::new($router)
		->withDebugMode(true)
		->layer(CorsMiddleware::permissive())
		->layer(FaviconMiddleware::noContent());

	echo "Starting server on 0.0.0.0:8888...\n";

	$server->serve('0.0.0.0:8888');
}
