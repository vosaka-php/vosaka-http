***

# HttpServer





* Full name: `\vosaka\http\server\HttpServer`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### listener



```php
private ?\venndev\vosaka\net\tcp\TCPListener $listener
```






***

### running



```php
private bool $running
```






***

### router



```php
private \vosaka\http\router\Router $router
```






***

### middlewareStack



```php
private \vosaka\http\middleware\MiddlewareStack $middlewareStack
```






***

### errorHandlers



```php
private \vosaka\http\server\ErrorHandlerManager $errorHandlers
```






***

### config



```php
private \vosaka\http\server\ServerConfig $config
```






***

### debugMode



```php
private bool $debugMode
```






***

### requestParser



```php
private \vosaka\http\server\HttpRequestParser $requestParser
```






***

### responseWriter



```php
private \vosaka\http\server\HttpResponseWriter $responseWriter
```






***

### requestProcessor



```php
private \vosaka\http\server\RequestProcessor $requestProcessor
```






***

### debugHelper



```php
private \vosaka\http\server\ServerDebugHelper $debugHelper
```






***

## Methods


### __construct



```php
public __construct(\vosaka\http\router\Router $router, ?\vosaka\http\server\ServerConfig $config = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\router\Router** |  |
| `$config` | **?\vosaka\http\server\ServerConfig** |  |





***

### new



```php
public static new(\vosaka\http\router\Router $router, ?\vosaka\http\server\ServerConfig $config = null): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\router\Router** |  |
| `$config` | **?\vosaka\http\server\ServerConfig** |  |





***

### bind



```php
public bind(string $address): \vosaka\http\server\ServerBuilder
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** |  |





***

### withErrorHandler



```php
public withErrorHandler(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### withNotFoundHandler



```php
public withNotFoundHandler(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### withMethodNotAllowedHandler



```php
public withMethodNotAllowedHandler(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### withDebugMode



```php
public withDebugMode(bool $debug = true): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$debug` | **bool** |  |





***

### layer



```php
public layer(\vosaka\http\middleware\MiddlewareInterface|\Closure $middleware): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middleware` | **\vosaka\http\middleware\MiddlewareInterface&#124;\Closure** |  |





***

### serve



```php
public serve(string $address, array $options = []): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** |  |
| `$options` | **array** |  |





***

### shutdown



```php
public shutdown(): void
```












***

### handleConnection



```php
private handleConnection(\venndev\vosaka\net\tcp\TCPStream $client): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***


***
> Automatically generated on 2025-07-01
