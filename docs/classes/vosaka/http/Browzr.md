***

# Browzr

VOsaka HTTP Library - Main Facade

Browzr serves as the main entry point and facade for the VOsaka HTTP library.
It provides convenient static methods for common HTTP operations including
client requests, server creation, and message manipulation.

* Full name: `\vosaka\http\Browzr`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### defaultClient



```php
private static ?\vosaka\http\client\HttpClient $defaultClient
```



* This property is **static**.


***

## Methods


### client

Create a new HTTP client with optional configuration.

```php
public static client(array $options = []): \vosaka\http\client\HttpClient
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** |  |





***

### server



```php
public static server(\vosaka\http\server\Router $router, ?\vosaka\http\server\ServerConfig $config = null): mixed
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\server\Router** |  |
| `$config` | **?\vosaka\http\server\ServerConfig** |  |





***

### getDefaultClient

Get the default HTTP client instance (singleton).

```php
public static getDefaultClient(): \vosaka\http\client\HttpClient
```



* This method is **static**.








***

### get

Send a GET request asynchronously.

```php
public static get(string $url, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### post

Send a POST request asynchronously.

```php
public static post(string $url, mixed $body = null, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$body` | **mixed** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### put

Send a PUT request asynchronously.

```php
public static put(string $url, mixed $body = null, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$body` | **mixed** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### delete

Send a DELETE request asynchronously.

```php
public static delete(string $url, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### patch

Send a PATCH request asynchronously.

```php
public static patch(string $url, mixed $body = null, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$body` | **mixed** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### head

Send a HEAD request asynchronously.

```php
public static head(string $url, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### options

Send an OPTIONS request asynchronously.

```php
public static options(string $url, array $headers = [], array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$headers` | **array** |  |
| `$options` | **array** |  |





***

### send

Send a custom HTTP request asynchronously.

```php
public static send(\Psr\Http\Message\RequestInterface $request, array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\RequestInterface** |  |
| `$options` | **array** |  |





***

### request

Create a new HTTP request.

```php
public static request(string $method, string $uri, array $headers = [], mixed $body = null, string $protocolVersion = &quot;1.1&quot;): \vosaka\http\message\Request
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |
| `$uri` | **string** |  |
| `$headers` | **array** |  |
| `$body` | **mixed** |  |
| `$protocolVersion` | **string** |  |





***

### response

Create a new HTTP response.

```php
public static response(int $statusCode = 200, array $headers = [], mixed $body = null, string $protocolVersion = &quot;1.1&quot;, string $reasonPhrase = &quot;&quot;): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |
| `$headers` | **array** |  |
| `$body` | **mixed** |  |
| `$protocolVersion` | **string** |  |
| `$reasonPhrase` | **string** |  |





***

### serverRequest

Create a new server request.

```php
public static serverRequest(string $method, string $uri, array $headers = [], mixed $body = null, string $protocolVersion = &quot;1.1&quot;, array $serverParams = []): \vosaka\http\message\ServerRequest
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |
| `$uri` | **string** |  |
| `$headers` | **array** |  |
| `$body` | **mixed** |  |
| `$protocolVersion` | **string** |  |
| `$serverParams` | **array** |  |





***

### serverRequestFromGlobals

Create a server request from global variables.

```php
public static serverRequestFromGlobals(): \vosaka\http\message\ServerRequest
```



* This method is **static**.








***

### uri

Create a new URI.

```php
public static uri(string $uri): \vosaka\http\message\Uri
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uri` | **string** |  |





***

### stream

Create a new stream.

```php
public static stream(string $content = &quot;&quot;): \vosaka\http\message\Stream
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$content` | **string** |  |





***

### streamFromFile

Create a stream from a file.

```php
public static streamFromFile(string $filename, string $mode = &quot;r&quot;): \vosaka\http\message\Stream
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string** |  |
| `$mode` | **string** |  |





***

### streamFromResource

Create a stream from a resource.

```php
public static streamFromResource(mixed $resource): \vosaka\http\message\Stream
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$resource` | **mixed** |  |





***

### json

Create a JSON response.

```php
public static json(mixed $data, int $statusCode = 200, array $headers = []): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **mixed** |  |
| `$statusCode` | **int** |  |
| `$headers` | **array** |  |





***

### html

Create an HTML response.

```php
public static html(string $html, int $statusCode = 200, array $headers = []): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$html` | **string** |  |
| `$statusCode` | **int** |  |
| `$headers` | **array** |  |





***

### text

Create a plain text response.

```php
public static text(string $text, int $statusCode = 200, array $headers = []): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$text` | **string** |  |
| `$statusCode` | **int** |  |
| `$headers` | **array** |  |





***

### redirect

Create a redirect response.

```php
public static redirect(string $url, int $statusCode = 302): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$statusCode` | **int** |  |





***

### notFound

Create a 404 Not Found response.

```php
public static notFound(string $message = &quot;Not Found&quot;): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |





***

### serverError

Create a 500 Internal Server Error response.

```php
public static serverError(string $message = &quot;Internal Server Error&quot;): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |





***

### badRequest

Create a 400 Bad Request response.

```php
public static badRequest(string $message = &quot;Bad Request&quot;): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |





***

### unauthorized

Create a 401 Unauthorized response.

```php
public static unauthorized(string $message = &quot;Unauthorized&quot;): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |





***

### forbidden

Create a 403 Forbidden response.

```php
public static forbidden(string $message = &quot;Forbidden&quot;): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |





***

### unprocessableEntity

Create a 422 Unprocessable Entity response.

```php
public static unprocessableEntity(mixed $errors, string $message = &quot;Unprocessable Entity&quot;): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$errors` | **mixed** |  |
| `$message` | **string** |  |





***

### tooManyRequests

Create a 429 Too Many Requests response.

```php
public static tooManyRequests(string $message = &quot;Too Many Requests&quot;, ?int $retryAfter = null): \vosaka\http\message\Response
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |
| `$retryAfter` | **?int** |  |





***

### setDefaultClient

Set the default HTTP client.

```php
public static setDefaultClient(\vosaka\http\client\HttpClient $client): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\vosaka\http\client\HttpClient** |  |





***

### resetDefaultClient

Reset the default HTTP client to null.

```php
public static resetDefaultClient(): void
```



* This method is **static**.








***

### version

Get library version information.

```php
public static version(): array
```



* This method is **static**.








***


***
> Automatically generated on 2025-07-01
