***

# RequestProcessor





* Full name: `\vosaka\http\server\RequestProcessor`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


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

### debugMode



```php
private bool $debugMode
```






***

## Methods


### __construct



```php
public __construct(\vosaka\http\router\Router $router, \vosaka\http\middleware\MiddlewareStack $middlewareStack, \vosaka\http\server\ErrorHandlerManager $errorHandlers, bool $debugMode = false): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\router\Router** |  |
| `$middlewareStack` | **\vosaka\http\middleware\MiddlewareStack** |  |
| `$errorHandlers` | **\vosaka\http\server\ErrorHandlerManager** |  |
| `$debugMode` | **bool** |  |





***

### processRequest



```php
public processRequest(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### findAllowedMethods



```php
private findAllowedMethods(\Psr\Http\Message\ServerRequestInterface $request): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### enrichRequestWithRouteData



```php
private enrichRequestWithRouteData(\Psr\Http\Message\ServerRequestInterface $request, \vosaka\http\router\RouteMatch $match): \Psr\Http\Message\ServerRequestInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$match` | **\vosaka\http\router\RouteMatch** |  |





***

### logRouteMatch



```php
private logRouteMatch(\Psr\Http\Message\ServerRequestInterface $request, \vosaka\http\router\RouteMatch $match): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$match` | **\vosaka\http\router\RouteMatch** |  |





***

### convertToResponse



```php
private convertToResponse(mixed $result): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$result` | **mixed** |  |





***


***
> Automatically generated on 2025-07-01
