***

# Router

Enhanced Router with comprehensive parameter support

Features:
- Required parameters: /users/{id}
- Optional parameters: /users/{id?}
- Constrained parameters: /users/{id:\d+}
- Multiple constraints: /users/{id:\d+}/{slug:[a-z-]+}
- Wildcard routes: /files/*
- Named route groups
- Query parameter handling
- Route caching for performance

* Full name: `\vosaka\http\server\Router`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### routes



```php
private array $routes
```






***

### namedRoutes



```php
private array $namedRoutes
```






***

### globalMiddleware



```php
private array $globalMiddleware
```






***

### compiledCache



```php
private array $compiledCache
```






***

### prefix



```php
private string $prefix
```






***

### middlewareStack



```php
private ?\vosaka\http\middleware\MiddlewareStack $middlewareStack
```






***

## Methods


### __construct



```php
public __construct(?\vosaka\http\middleware\MiddlewareStack $middlewareStack = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middlewareStack` | **?\vosaka\http\middleware\MiddlewareStack** |  |





***

### new



```php
public static new(): self
```



* This method is **static**.








***

### route

Add a route with method, pattern and handler

```php
public route(string $method, string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### get



```php
public get(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### post



```php
public post(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### put



```php
public put(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### delete



```php
public delete(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### patch



```php
public patch(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### head



```php
public head(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### options



```php
public options(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### match

Match multiple methods for the same pattern

```php
public match(array $methods, string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$methods` | **array** |  |
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### any

Match any HTTP method

```php
public any(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### middleware

Add middleware to the last added route

```php
public middleware(\vosaka\http\middleware\MiddlewareInterface|\Closure $middleware): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middleware` | **\vosaka\http\middleware\MiddlewareInterface&#124;\Closure** |  |





***

### group

Create a route group with shared prefix and middleware

```php
public group(string $prefix, \Closure $callback, array $middleware = []): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$prefix` | **string** |  |
| `$callback` | **\Closure** |  |
| `$middleware` | **array** |  |





***

### layer

Add middleware that applies to all routes

```php
public layer(\vosaka\http\middleware\MiddlewareInterface|\Closure $middleware): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middleware` | **\vosaka\http\middleware\MiddlewareInterface&#124;\Closure** |  |





***

### mount

Mount another router with a prefix

```php
public mount(string $prefix, \vosaka\http\server\Router $router): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$prefix` | **string** |  |
| `$router` | **\vosaka\http\server\Router** |  |





***

### handle

Handle incoming request and return response

```php
public handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### findMatch

Find matching route for request

```php
public findMatch(\Psr\Http\Message\ServerRequestInterface $request): ?\vosaka\http\server\RouteMatch
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### url

Generate URL for named route

```php
public url(string $name, array $params = [], array $query = []): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$params` | **array** |  |
| `$query` | **array** |  |





***

### hasRoute

Check if route exists

```php
public hasRoute(string $name): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### getRoutes

Get all routes

```php
public getRoutes(): array
```












***

### getRoute

Get route by name

```php
public getRoute(string $name): ?\vosaka\http\server\RouteDefinition
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### getRoutesByMethod

Get routes organized by method for debugging

```php
public getRoutesByMethod(): array
```












***

### buildHandler

Build middleware handler chain

```php
private buildHandler(\vosaka\http\server\RouteDefinition $route, \Psr\Http\Message\ServerRequestInterface $request): \Closure
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$route` | **\vosaka\http\server\RouteDefinition** |  |
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### compilePattern

FIXED: Completely rewritten pattern compilation method

```php
private compilePattern(string $pattern): \vosaka\http\server\CompiledRoute
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |





***

### matchPattern

Enhanced pattern matching using named capture groups

```php
private matchPattern(\vosaka\http\server\CompiledRoute $compiled, string $path): ?array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$compiled` | **\vosaka\http\server\CompiledRoute** |  |
| `$path` | **string** |  |





***

### buildUrl

Enhanced URL building with better parameter handling

```php
private buildUrl(string $pattern, array $params): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$params` | **array** |  |





***

### convertToResponse

Convert handler result to proper response

```php
private convertToResponse(mixed $result): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$result` | **mixed** |  |





***


***
> Automatically generated on 2025-07-01
