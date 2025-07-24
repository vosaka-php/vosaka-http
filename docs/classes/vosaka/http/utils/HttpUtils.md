***

# HttpUtils

HTTP utility functions for common tasks.

This class provides static utility methods for working with HTTP
messages, headers, content types, and other common HTTP operations.

* Full name: `\vosaka\http\utils\HttpUtils`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### mimeTypes



```php
private static array&lt;string,string&gt; $mimeTypes
```



* This property is **static**.


***

### statusTexts



```php
private static array&lt;int,string&gt; $statusTexts
```



* This property is **static**.


***

## Methods


### getMimeType

Get MIME type for a file extension.

```php
public static getMimeType(string $extension): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$extension` | **string** |  |





***

### getMimeTypeFromPath

Get MIME type from a file path.

```php
public static getMimeTypeFromPath(string $path): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### getStatusText

Get status text for HTTP status code.

```php
public static getStatusText(int $statusCode): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |





***

### isInformational

Check if HTTP status code is informational (1xx).

```php
public static isInformational(int $statusCode): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |





***

### isSuccessful

Check if HTTP status code is successful (2xx).

```php
public static isSuccessful(int $statusCode): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |





***

### isRedirection

Check if HTTP status code is a redirection (3xx).

```php
public static isRedirection(int $statusCode): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |





***

### isClientError

Check if HTTP status code is a client error (4xx).

```php
public static isClientError(int $statusCode): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |





***

### isServerError

Check if HTTP status code is a server error (5xx).

```php
public static isServerError(int $statusCode): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statusCode` | **int** |  |





***

### parseAcceptHeader

Parse HTTP Accept header and return accepted types with quality scores.

```php
public static parseAcceptHeader(string $accept): array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$accept` | **string** |  |





***

### negotiateContentType

Find the best matching content type based on Accept header.

```php
public static negotiateContentType(string $accept, array $availableTypes): ?string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$accept` | **string** |  |
| `$availableTypes` | **array** |  |





***

### matchMediaType

Check if a media type matches a pattern (with wildcards).

```php
public static matchMediaType(string $pattern, string $type): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$type` | **string** |  |





***

### parseCookies

Parse cookies from Cookie header.

```php
public static parseCookies(string $cookieHeader): array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$cookieHeader` | **string** |  |





***

### formatSetCookie

Format cookies for Set-Cookie header.

```php
public static formatSetCookie(string $name, string $value, array $options = []): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$value` | **string** |  |
| `$options` | **array** |  |





***

### parseQuery

Parse query string into an array.

```php
public static parseQuery(string $query): array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **string** |  |





***

### buildQuery

Build query string from array.

```php
public static buildQuery(array $params): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$params` | **array** |  |





***

### parseBasicAuth

Parse basic authentication header.

```php
public static parseBasicAuth(string $authHeader): ?array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$authHeader` | **string** |  |





***

### parseBearerToken

Parse bearer token from Authorization header.

```php
public static parseBearerToken(string $authHeader): ?string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$authHeader` | **string** |  |





***

### isAjax

Check if request is an AJAX request.

```php
public static isAjax(\Psr\Http\Message\ServerRequestInterface $request): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### isHttps

Check if request is HTTPS.

```php
public static isHttps(\Psr\Http\Message\ServerRequestInterface $request): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### getClientIp

Get client IP address from request.

```php
public static getClientIp(\Psr\Http\Message\ServerRequestInterface $request): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### download

Create a file download response.

```php
public static download(string $filePath, ?string $filename = null, array $headers = []): \Psr\Http\Message\ResponseInterface
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filePath` | **string** |  |
| `$filename` | **?string** |  |
| `$headers` | **array** |  |





***

### serverSentEvents

Create a server-sent events response.

```php
public static serverSentEvents(): \Psr\Http\Message\ResponseInterface
```



* This method is **static**.








***

### formatSseData

Format data for server-sent events.

```php
public static formatSseData(mixed $data, ?string $event = null, ?string $id = null, ?int $retry = null): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **mixed** |  |
| `$event` | **?string** |  |
| `$id` | **?string** |  |
| `$retry` | **?int** |  |





***

### isValidMethod

Validate HTTP method.

```php
public static isValidMethod(string $method): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |





***

### sanitizeFilename

Sanitize filename for safe usage.

```php
public static sanitizeFilename(string $filename): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string** |  |





***


***
> Automatically generated on 2025-07-24
