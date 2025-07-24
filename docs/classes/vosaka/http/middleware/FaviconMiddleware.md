***

# FaviconMiddleware

Favicon middleware.

This middleware handles favicon.ico requests automatically to prevent
404 errors and reduce server load from browser favicon requests.

* Full name: `\vosaka\http\middleware\FaviconMiddleware`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\vosaka\http\middleware\MiddlewareInterface`](./MiddlewareInterface.md)
* This class is a **Final class**



## Properties


### faviconPath



```php
private ?string $faviconPath
```






***

### faviconData



```php
private ?string $faviconData
```






***

### faviconLoaded



```php
private bool $faviconLoaded
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

### isFaviconRequest



```php
private isFaviconRequest(\Psr\Http\Message\ServerRequestInterface $request): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### handleFaviconRequest



```php
private handleFaviconRequest(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### loadFavicon



```php
private loadFavicon(): void
```












***

### createFaviconResponse



```php
private createFaviconResponse(): \Psr\Http\Message\ResponseInterface
```












***

### createEmptyResponse



```php
private createEmptyResponse(): \Psr\Http\Message\ResponseInterface
```












***

### setFaviconData

Set favicon data directly

```php
public setFaviconData(string $data): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### setFaviconPath

Set favicon file path

```php
public setFaviconPath(string $path): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### noContent

Create favicon middleware that returns 204 No Content

```php
public static noContent(): self
```



* This method is **static**.








***

### notFound

Create favicon middleware that returns 404 Not Found

```php
public static notFound(): self
```



* This method is **static**.








***

### fromFile

Create favicon middleware with custom favicon file

```php
public static fromFile(string $filePath): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filePath` | **string** |  |





***

### fromData

Create favicon middleware with favicon data

```php
public static fromData(string $data): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### customPath

Create favicon middleware with custom path

```php
public static customPath(string $path): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***


***
> Automatically generated on 2025-07-24
