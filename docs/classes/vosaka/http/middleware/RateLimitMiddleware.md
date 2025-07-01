***

# RateLimitMiddleware

Rate limiting middleware for request throttling.

This middleware implements token bucket algorithm to limit the number
of requests per client within a specified time window. It helps protect
against abuse and ensures fair resource usage.

* Full name: `\vosaka\http\middleware\RateLimitMiddleware`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\vosaka\http\middleware\MiddlewareInterface`](./MiddlewareInterface.md)
* This class is a **Final class**



## Properties


### buckets



```php
private array $buckets
```






***

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

### generateKey



```php
private generateKey(\Psr\Http\Message\ServerRequestInterface $request): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### getClientIp



```php
private getClientIp(\Psr\Http\Message\ServerRequestInterface $request): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### getBucket



```php
private getBucket(string $key): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **string** |  |





***

### refillBucket



```php
private refillBucket(array& $bucket, int $now): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bucket` | **array** |  |
| `$now` | **int** |  |





***

### isAllowed



```php
private isAllowed(array $bucket): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bucket` | **array** |  |





***

### consumeToken



```php
private consumeToken(array& $bucket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bucket` | **array** |  |





***

### isSuccessfulResponse



```php
private isSuccessfulResponse(\Psr\Http\Message\ResponseInterface $response): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***

### createRateLimitResponse



```php
private createRateLimitResponse(array $bucket): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bucket` | **array** |  |





***

### addRateLimitHeaders



```php
private addRateLimitHeaders(\Psr\Http\Message\ResponseInterface $response, array $bucket): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |
| `$bucket` | **array** |  |





***

### calculateRetryAfter



```php
private calculateRetryAfter(array $bucket): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bucket` | **array** |  |





***

### lenient

Create rate limit middleware with lenient settings.

```php
public static lenient(): self
```



* This method is **static**.








***

### strict

Create rate limit middleware with strict settings.

```php
public static strict(): self
```



* This method is **static**.








***

### api

Create rate limit middleware for API endpoints.

```php
public static api(int $requestsPerHour = 100): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$requestsPerHour` | **int** |  |





***

### cleanup

Clean up expired buckets to prevent memory leaks.

```php
public cleanup(): void
```












***


***
> Automatically generated on 2025-07-01
