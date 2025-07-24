***

# Response

PSR-7 Response implementation for HTTP messages.

This class represents an HTTP response message with status code, reason phrase,
headers, and body. It implements the PSR-7 ResponseInterface for compatibility
with HTTP message standards.

* Full name: `\vosaka\http\message\Response`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\Psr\Http\Message\ResponseInterface`](../../../Psr/Http/Message/ResponseInterface.md)
* This class is a **Final class**



## Properties


### statusCode



```php
private int $statusCode
```






***

### reasonPhrase



```php
private string $reasonPhrase
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

### phrases



```php
private static array&lt;int,string&gt; $phrases
```



* This property is **static**.


***

## Methods


### __construct



```php
public __construct(int $statusCode = 200, array $headers = [], \Psr\Http\Message\StreamInterface|string|null $body = null, string $protocolVersion = &#039;1.1&#039;, string $reasonPhrase = &#039;&#039;): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |
| `$headers` | **array** |  |
| `$body` | **\Psr\Http\Message\StreamInterface&#124;string&#124;null** |  |
| `$protocolVersion` | **string** |  |
| `$reasonPhrase` | **string** |  |





***

### getStatusCode



```php
public getStatusCode(): int
```












***

### withStatus



```php
public withStatus(int $code, string $reasonPhrase = &#039;&#039;): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$code` | **int** |  |
| `$reasonPhrase` | **string** |  |





***

### getReasonPhrase



```php
public getReasonPhrase(): string
```












***

### getProtocolVersion



```php
public getProtocolVersion(): string
```












***

### withProtocolVersion



```php
public withProtocolVersion(string $version): \Psr\Http\Message\ResponseInterface
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
public withHeader(string $name, mixed $value): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$value` | **mixed** |  |





***

### withAddedHeader



```php
public withAddedHeader(string $name, mixed $value): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$value` | **mixed** |  |





***

### withoutHeader



```php
public withoutHeader(string $name): \Psr\Http\Message\ResponseInterface
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
public withBody(\Psr\Http\Message\StreamInterface $body): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$body` | **\Psr\Http\Message\StreamInterface** |  |





***

### validateStatusCode



```php
private validateStatusCode(int $code): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$code` | **int** |  |





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

### json

Create a JSON response.

```php
public static json(mixed $data, int $statusCode = 200, array $headers = []): self
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
public static html(string $html, int $statusCode = 200, array $headers = []): self
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

Create a text response.

```php
public static text(string $text, int $statusCode = 200, array $headers = []): self
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
public static redirect(string $url, int $statusCode = 302): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** |  |
| `$statusCode` | **int** |  |





***


***
> Automatically generated on 2025-07-24
