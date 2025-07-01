***

# CorsMiddleware

CORS (Cross-Origin Resource Sharing) middleware.

This middleware handles CORS preflight requests and adds appropriate
CORS headers to responses to enable cross-origin requests from browsers.

* Full name: `\vosaka\http\middleware\CorsMiddleware`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\vosaka\http\middleware\MiddlewareInterface`](./MiddlewareInterface.md)
* This class is a **Final class**



## Properties


### options



```php
private array $options
```






***

## Methods


### __construct



```php
public __construct(array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** |  |





***

### process

Process an incoming server request.

```php
public process(\Psr\Http\Message\ServerRequestInterface $request, callable $next): \Psr\Http\Message\ResponseInterface|null
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** | The request to process |
| `$next` | **callable** | The next middleware/handler in the chain |


**Return Value:**

Return a response to short-circuit, or null to continue




***

### handlePreflightRequest



```php
private handlePreflightRequest(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### addCorsHeaders



```php
private addCorsHeaders(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***

### getAllowedOrigin



```php
private getAllowedOrigin(\Psr\Http\Message\ServerRequestInterface $request): ?string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### permissive

Create CORS middleware with permissive settings for development.

```php
public static permissive(): self
```



* This method is **static**.








***

### strict

Create CORS middleware with strict settings for production.

```php
public static strict(array $allowedOrigins = []): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$allowedOrigins` | **array** |  |





***


***
> Automatically generated on 2025-07-01
