***

# ServerRequest

PSR-7 ServerRequest implementation for HTTP messages.

This class represents an HTTP server request with additional server-side
specific data such as query parameters, parsed body, uploaded files,
and server parameters.

* Full name: `\vosaka\http\message\ServerRequest`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\Psr\Http\Message\ServerRequestInterface`](../../../Psr/Http/Message/ServerRequestInterface.md)
* This class is a **Final class**



## Properties


### method



```php
private string $method
```






***

### uri



```php
private \Psr\Http\Message\UriInterface $uri
```






***

### protocolVersion



```php
private string $protocolVersion
```






***

### headers



```php
private array $headers
```






***

### headerNames



```php
private array $headerNames
```






***

### body



```php
private \Psr\Http\Message\StreamInterface $body
```






***

### requestTarget



```php
private ?string $requestTarget
```






***

### serverParams



```php
private array $serverParams
```






***

### cookieParams



```php
private array $cookieParams
```






***

### queryParams



```php
private array $queryParams
```






***

### uploadedFiles



```php
private array $uploadedFiles
```






***

### parsedBody



```php
private mixed $parsedBody
```






***

### attributes



```php
private array $attributes
```






***

### validMethods



```php
private static array&lt;string,bool&gt; $validMethods
```



* This property is **static**.


***

## Methods


### __construct



```php
public __construct(string $method, \Psr\Http\Message\UriInterface|string $uri, array $headers = [], \Psr\Http\Message\StreamInterface|string|null $body = null, string $protocolVersion = &quot;1.1&quot;, array $serverParams = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |
| `$uri` | **\Psr\Http\Message\UriInterface&#124;string** |  |
| `$headers` | **array** |  |
| `$body` | **\Psr\Http\Message\StreamInterface&#124;string&#124;null** |  |
| `$protocolVersion` | **string** |  |
| `$serverParams` | **array** |  |





***

### getMethod



```php
public getMethod(): string
```












***

### withMethod



```php
public withMethod(string $method): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |





***

### getUri



```php
public getUri(): \Psr\Http\Message\UriInterface
```












***

### withUri



```php
public withUri(\Psr\Http\Message\UriInterface $uri, bool $preserveHost = false): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uri` | **\Psr\Http\Message\UriInterface** |  |
| `$preserveHost` | **bool** |  |





***

### getRequestTarget



```php
public getRequestTarget(): string
```












***

### withRequestTarget



```php
public withRequestTarget(string $requestTarget): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$requestTarget` | **string** |  |





***

### getProtocolVersion



```php
public getProtocolVersion(): string
```












***

### withProtocolVersion



```php
public withProtocolVersion(string $version): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$version` | **string** |  |





***

### getHeaders



```php
public getHeaders(): array
```












***

### hasHeader



```php
public hasHeader(string $name): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### getHeader



```php
public getHeader(string $name): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### getHeaderLine



```php
public getHeaderLine(string $name): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### withHeader



```php
public withHeader(string $name, mixed $value): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$value` | **mixed** |  |





***

### withAddedHeader



```php
public withAddedHeader(string $name, mixed $value): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$value` | **mixed** |  |





***

### withoutHeader



```php
public withoutHeader(string $name): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### getBody



```php
public getBody(): \Psr\Http\Message\StreamInterface
```












***

### withBody



```php
public withBody(\Psr\Http\Message\StreamInterface $body): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$body` | **\Psr\Http\Message\StreamInterface** |  |





***

### getServerParams



```php
public getServerParams(): array
```












***

### getCookieParams



```php
public getCookieParams(): array
```












***

### withCookieParams



```php
public withCookieParams(array $cookies): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$cookies` | **array** |  |





***

### getQueryParams



```php
public getQueryParams(): array
```












***

### withQueryParams



```php
public withQueryParams(array $query): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **array** |  |





***

### getUploadedFiles



```php
public getUploadedFiles(): array
```












***

### withUploadedFiles



```php
public withUploadedFiles(array $uploadedFiles): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uploadedFiles` | **array** |  |





***

### getParsedBody



```php
public getParsedBody(): mixed
```












***

### withParsedBody



```php
public withParsedBody(mixed $data): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **mixed** |  |





***

### getAttributes



```php
public getAttributes(): array
```












***

### getAttribute



```php
public getAttribute(string $name, mixed $default = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$default` | **mixed** |  |





***

### withAttribute



```php
public withAttribute(string $name, mixed $value): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$value` | **mixed** |  |





***

### withoutAttribute



```php
public withoutAttribute(string $name): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### validateMethod



```php
private validateMethod(string $method): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |





***

### setHeaders



```php
private setHeaders(array $headers): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$headers` | **array** |  |





***

### validateHeaderName



```php
private validateHeaderName(string $name): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### validateAndTrimHeaderValues



```php
private validateAndTrimHeaderValues(mixed $values): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$values` | **mixed** |  |





***

### initializeBody



```php
private initializeBody(mixed $body): \Psr\Http\Message\StreamInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$body` | **mixed** |  |





***

### updateHostFromUri



```php
private updateHostFromUri(): void
```












***

### validateUploadedFiles



```php
private validateUploadedFiles(array $uploadedFiles): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uploadedFiles` | **array** |  |





***

### fromGlobals

Create a ServerRequest from global variables.

```php
public static fromGlobals(): self
```



* This method is **static**.








***

### getUriFromGlobals



```php
private static getUriFromGlobals(): \vosaka\http\message\Uri
```



* This method is **static**.








***


***
> Automatically generated on 2025-07-01
