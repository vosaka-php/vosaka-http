***

# ErrorHandlerManager





* Full name: `\vosaka\http\server\ErrorHandlerManager`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### errorHandler



```php
private ?\Closure $errorHandler
```






***

### notFoundHandler



```php
private ?\Closure $notFoundHandler
```






***

### methodNotAllowedHandler



```php
private ?\Closure $methodNotAllowedHandler
```






***

### router



```php
private \vosaka\http\router\Router $router
```






***

### debugMode



```php
private bool $debugMode
```






***

## Methods


### __construct



```php
public __construct(\vosaka\http\router\Router $router, bool $debugMode = false): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\router\Router** |  |
| `$debugMode` | **bool** |  |





***

### setErrorHandler



```php
public setErrorHandler(\Closure $handler): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### setNotFoundHandler



```php
public setNotFoundHandler(\Closure $handler): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### setMethodNotAllowedHandler



```php
public setMethodNotAllowedHandler(\Closure $handler): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### handleError



```php
public handleError(\Throwable $error, ?\Psr\Http\Message\ServerRequestInterface $request = null): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$error` | **\Throwable** |  |
| `$request` | **?\Psr\Http\Message\ServerRequestInterface** |  |





***

### handleNotFound



```php
public handleNotFound(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### handleMethodNotAllowed



```php
public handleMethodNotAllowed(\Psr\Http\Message\ServerRequestInterface $request, array $allowedMethods): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$allowedMethods` | **array** |  |





***

### defaultNotFoundHandler



```php
private defaultNotFoundHandler(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### defaultMethodNotAllowedHandler



```php
private defaultMethodNotAllowedHandler(\Psr\Http\Message\ServerRequestInterface $request, array $allowedMethods): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$allowedMethods` | **array** |  |





***

### defaultErrorHandler



```php
private defaultErrorHandler(\Throwable $error, ?\Psr\Http\Message\ServerRequestInterface $request = null): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$error` | **\Throwable** |  |
| `$request` | **?\Psr\Http\Message\ServerRequestInterface** |  |





***


***
> Automatically generated on 2025-07-01
