***

# Router

Enhanced Router with comprehensive parameter support
Now refactored into smaller, focused classes



* Full name: `\vosaka\http\router\Router`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**


## Constants

| Constant | Visibility | Type | Value |
|:---------|:-----------|:-----|:------|
|`MAX_CACHE_SIZE`|private| |8000|

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

### methodRoutes



```php
private array $methodRoutes
```






***

### routeCache



```php
private array $routeCache
```






***

### cacheSize



```php
private int $cacheSize
```






***

### prefix



```php
private string $prefix
```






***

### compiler



```php
private \vosaka\http\router\PatternCompiler $compiler
```






***

### matcher



```php
private \vosaka\http\router\RouteMatcher $matcher
```






***

### urlGenerator



```php
private \vosaka\http\utils\UrlGenerator $urlGenerator
```






***

### responseConverter



```php
private \vosaka\http\message\ResponseConverter $responseConverter
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

### getMatcher



```php
public getMatcher(): \vosaka\http\router\RouteMatcher
```












***

### new



```php
public static new(): self
```



* This method is **static**.








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

### route



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

### match



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



```php
public middleware(\vosaka\http\middleware\MiddlewareInterface|\Closure $middleware): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middleware` | **\vosaka\http\middleware\MiddlewareInterface&#124;\Closure** |  |





***

### group



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



```php
public layer(\vosaka\http\middleware\MiddlewareInterface|\Closure $middleware): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middleware` | **\vosaka\http\middleware\MiddlewareInterface&#124;\Closure** |  |





***

### mount



```php
public mount(string $prefix, \vosaka\http\router\Router $router): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$prefix` | **string** |  |
| `$router` | **\vosaka\http\router\Router** |  |





***

### handle



```php
public handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### url



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



```php
public hasRoute(string $name): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### getRoutes



```php
public getRoutes(?string $method = null): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **?string** |  |





***

### getRoute



```php
public getRoute(string $name): ?\vosaka\http\router\RouteDefinition
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### getRoutesByMethod



```php
public getRoutesByMethod(): array
```












***

### buildHandler



```php
private buildHandler(\vosaka\http\router\RouteDefinition $route, \Psr\Http\Message\ServerRequestInterface $request): \Closure
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$route` | **\vosaka\http\router\RouteDefinition** |  |
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### updateUrlGenerator



```php
private updateUrlGenerator(): void
```












***


***
> Automatically generated on 2025-07-24
