***

# HttpClient

Asynchronous HTTP Client using cURL Multi with VOsaka runtime.

This client provides async HTTP request capabilities using cURL Multi
handle for non-blocking operations. It supports GET, POST, PUT, DELETE
and other HTTP methods with configurable timeouts, headers, and SSL options.

* Full name: `\vosaka\http\client\HttpClient`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### defaultOptions



```php
private array $defaultOptions
```






***

### multiHandle



```php
private static mixed $multiHandle
```



* This property is **static**.


***

### activeHandles



```php
private static array $activeHandles
```



* This property is **static**.


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

### send

Send an HTTP request asynchronously.

```php
public send(\Psr\Http\Message\RequestInterface $request, array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\RequestInterface** |  |
| `$options` | **array** |  |





***

### get

Send a GET request.

```php
public get(string $url, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### post

Send a POST request.

```php
public post(string $url, mixed $body = null, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$body` | **mixed** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### put

Send a PUT request.

```php
public put(string $url, mixed $body = null, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$body` | **mixed** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### delete

Send a DELETE request.

```php
public delete(string $url, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### patch

Send a PATCH request.

```php
public patch(string $url, mixed $body = null, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$body` | **mixed** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### head

Send a HEAD request.

```php
public head(string $url, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### getDefaultCABundle

Get the default CA bundle path.

```php
private static getDefaultCABundle(): ?string
```

Tries multiple common locations.

* This method is **static**.








***

### downloadCABundle

Download CA bundle from curl.se

```php
private static downloadCABundle(): ?string
```



* This method is **static**.








***

### configureCurl

Configure cURL handle with request options.

```php
private configureCurl(mixed $ch, \Psr\Http\Message\RequestInterface $request, array $options): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ch` | **mixed** |  |
| `$request` | **\Psr\Http\Message\RequestInterface** |  |
| `$options` | **array** |  |





***

### executeAsync

Execute cURL request asynchronously.

```php
private executeAsync(mixed $ch): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ch` | **mixed** |  |





***

### parseResponse

Parse cURL response into Response object.

```php
private parseResponse(mixed $ch): \vosaka\http\message\Response
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ch` | **mixed** |  |





***

### buildHeaders

Build headers array for cURL.

```php
private buildHeaders(\Psr\Http\Message\RequestInterface $request, array $options): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\RequestInterface** |  |
| `$options` | **array** |  |





***

### prepareBody

Prepare request body.

```php
private prepareBody(mixed $body, array& $headers): \vosaka\http\message\Stream
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$body` | **mixed** |  |
| `$headers` | **array** |  |





***

### validateUri

Validate URI.

```php
private validateUri(mixed $uri): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uri` | **mixed** |  |





***

### __destruct

Cleanup mixeds on destruction.

```php
public __destruct(): mixed
```












***

### shutdown

Close the multi handle (call this when shutting down).

```php
public static shutdown(): void
```



* This method is **static**.








***


***
> Automatically generated on 2025-07-24
