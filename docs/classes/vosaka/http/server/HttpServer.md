***

# HttpServer





* Full name: `\vosaka\http\server\HttpServer`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### listener



```php
private ?\venndev\vosaka\net\tcp\TCPListener $listener
```






***

### running



```php
private bool $running
```






***

### router



```php
private \vosaka\http\server\Router $router
```






***

### middlewareStack



```php
private \vosaka\http\middleware\MiddlewareStack $middlewareStack
```






***

### errorHandler



```php
private ?\Closure $errorHandler
```






***

### notFoundHandler



```php
private ?\Closure $notFoundHandler
```






***

### methodNotAllowedHandler



```php
private ?\Closure $methodNotAllowedHandler
```






***

### config



```php
private \vosaka\http\server\ServerConfig $config
```






***

### debugMode



```php
private bool $debugMode
```






***

## Methods


### __construct



```php
public __construct(\vosaka\http\server\Router $router, ?\vosaka\http\server\ServerConfig $config = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\server\Router** |  |
| `$config` | **?\vosaka\http\server\ServerConfig** |  |





***

### new



```php
public static new(\vosaka\http\server\Router $router, ?\vosaka\http\server\ServerConfig $config = null): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\server\Router** |  |
| `$config` | **?\vosaka\http\server\ServerConfig** |  |





***

### bind

Create a server builder for fluent API

```php
public bind(string $address): \vosaka\http\server\ServerBuilder
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** |  |





***

### withErrorHandler



```php
public withErrorHandler(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### withNotFoundHandler



```php
public withNotFoundHandler(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### withMethodNotAllowedHandler



```php
public withMethodNotAllowedHandler(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### withDebugMode



```php
public withDebugMode(bool $debug = true): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$debug` | **bool** |  |





***

### layer

Add server-level middleware (runs before router)

```php
public layer(\vosaka\http\middleware\MiddlewareInterface|\Closure $middleware): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middleware` | **\vosaka\http\middleware\MiddlewareInterface&#124;\Closure** |  |





***

### serve

Start the server on the given address

```php
public serve(string $address, array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** |  |
| `$options` | **array** |  |





***

### shutdown



```php
public shutdown(): void
```












***

### handleConnection



```php
private handleConnection(\venndev\vosaka\net\tcp\TCPStream $client): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### processRequest

Enhanced request processing with proper Router integration

```php
private processRequest(\Psr\Http\Message\ServerRequestInterface $request): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### findAllowedMethods

Find allowed methods for a path (for 405 responses)

```php
private findAllowedMethods(\Psr\Http\Message\ServerRequestInterface $request): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### enrichRequestWithRouteData

Enrich request with route matching data

```php
private enrichRequestWithRouteData(\Psr\Http\Message\ServerRequestInterface $request, \vosaka\http\server\RouteMatch $match): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$match` | **\vosaka\http\server\RouteMatch** |  |





***

### printRouteTable

Debug: Print route table on startup

```php
private printRouteTable(): void
```












***

### logRouteMatch

Debug: Log route matches

```php
private logRouteMatch(\Psr\Http\Message\ServerRequestInterface $request, \vosaka\http\server\RouteMatch $match): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$match` | **\vosaka\http\server\RouteMatch** |  |





***

### defaultNotFoundHandler



```php
private defaultNotFoundHandler(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### defaultMethodNotAllowedHandler



```php
private defaultMethodNotAllowedHandler(\Psr\Http\Message\ServerRequestInterface $request, array $allowedMethods): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$allowedMethods` | **array** |  |





***

### defaultErrorHandler



```php
private defaultErrorHandler(\Throwable $error, ?\Psr\Http\Message\ServerRequestInterface $request = null): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$error` | **\Throwable** |  |
| `$request` | **?\Psr\Http\Message\ServerRequestInterface** |  |





***

### parseRequest



```php
private parseRequest(\venndev\vosaka\net\tcp\TCPStream $client): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### parseRequestLine



```php
private parseRequestLine(string $line): ?array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$line` | **string** |  |





***

### parseHeaders



```php
private parseHeaders(\venndev\vosaka\net\tcp\TCPStream $client): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### parseBody



```php
private parseBody(\venndev\vosaka\net\tcp\TCPStream $client, array $headers): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |
| `$headers` | **array** |  |





***

### buildServerParams



```php
private buildServerParams(string $method, string $target, string $version, \venndev\vosaka\net\tcp\TCPStream $client): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |
| `$target` | **string** |  |
| `$version` | **string** |  |
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### enrichRequest



```php
private enrichRequest(\Psr\Http\Message\ServerRequestInterface $request, \vosaka\http\message\Uri $uri, string $body): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$uri` | **\vosaka\http\message\Uri** |  |
| `$body` | **string** |  |





***

### sendResponse



```php
private sendResponse(\venndev\vosaka\net\tcp\TCPStream $client, \Psr\Http\Message\ResponseInterface $response): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***

### buildResponseHeaders



```php
private buildResponseHeaders(\Psr\Http\Message\ResponseInterface $response): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***

### getResponseBody



```php
private getResponseBody(\Psr\Http\Message\ResponseInterface $response): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***

### convertToResponse



```php
private convertToResponse(mixed $result): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$result` | **mixed** |  |





***

### parseUri



```php
private parseUri(string $target): \vosaka\http\message\Uri
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$target` | **string** |  |





***

### isFormData



```php
private isFormData(\Psr\Http\Message\ServerRequestInterface $request): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### shouldKeepAlive



```php
private shouldKeepAlive(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***


***
> Automatically generated on 2025-07-01
