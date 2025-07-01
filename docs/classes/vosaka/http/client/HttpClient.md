***

# HttpClient

Asynchronous HTTP Client using VOsaka runtime.

This client provides async HTTP request capabilities using the VOsaka
event loop system. It supports GET, POST, PUT, DELETE and other HTTP
methods with configurable timeouts, headers, and SSL options.

* Full name: `\vosaka\http\client\HttpClient`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### defaultOptions



```php
private array $defaultOptions
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

### options

Send a OPTIONS request.

```php
public options(string $url, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### sendSingleRequest



```php
private sendSingleRequest(\Psr\Http\Message\RequestInterface $request, array $options): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\RequestInterface** |  |
| `$options` | **array** |  |





***

### buildHttpRequest



```php
private buildHttpRequest(\Psr\Http\Message\RequestInterface $request, array $options): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\RequestInterface** |  |
| `$options` | **array** |  |





***

### readHttpResponse



```php
private readHttpResponse(\venndev\vosaka\net\tcp\TCPStream $stream): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### readChunkedBody



```php
private readChunkedBody(\venndev\vosaka\net\tcp\TCPStream $stream): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### getDefaultHeaders



```php
private getDefaultHeaders(array $options): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** |  |





***

### prepareBody



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



```php
private validateUri(mixed $uri): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uri` | **mixed** |  |





***

### isRedirectStatus



```php
private isRedirectStatus(int $statusCode): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |





***


***
> Automatically generated on 2025-07-01
