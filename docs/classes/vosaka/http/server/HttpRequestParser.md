***

# HttpRequestParser





* Full name: `\vosaka\http\server\HttpRequestParser`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### config



```php
private \vosaka\http\server\ServerConfig $config
```






***

## Methods


### __construct



```php
public __construct(\vosaka\http\server\ServerConfig $config): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$config` | **\vosaka\http\server\ServerConfig** |  |





***

### parseRequest



```php
public parseRequest(\venndev\vosaka\net\tcp\TCPStream $client): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### parseRequestLine



```php
private parseRequestLine(string $line): ?array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$line` | **string** |  |





***

### parseHeaders



```php
private parseHeaders(\venndev\vosaka\net\tcp\TCPStream $client): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### parseBody



```php
private parseBody(\venndev\vosaka\net\tcp\TCPStream $client, array $headers): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |
| `$headers` | **array** |  |





***

### buildServerParams



```php
private buildServerParams(string $method, string $target, string $version, \venndev\vosaka\net\tcp\TCPStream $client): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |
| `$target` | **string** |  |
| `$version` | **string** |  |
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### parseUri



```php
private parseUri(string $target): \vosaka\http\message\Uri
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$target` | **string** |  |





***

### enrichRequest



```php
private enrichRequest(\Psr\Http\Message\ServerRequestInterface $request, \vosaka\http\message\Uri $uri, string $body): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$uri` | **\vosaka\http\message\Uri** |  |
| `$body` | **string** |  |





***

### isFormData



```php
private isFormData(\Psr\Http\Message\ServerRequestInterface $request): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***


***
> Automatically generated on 2025-07-01
