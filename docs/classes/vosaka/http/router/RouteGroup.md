***

# RouteGroup

Route group helper for fluent API



* Full name: `\vosaka\http\router\RouteGroup`



## Properties


### router



```php
private \vosaka\http\router\Router $router
```






***

### prefix



```php
private string $prefix
```






***

## Methods


### __construct



```php
public __construct(\vosaka\http\router\Router $router, string $prefix): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\router\Router** |  |
| `$prefix` | **string** |  |





***

### get



```php
public get(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### post



```php
public post(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### put



```php
public put(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### delete



```php
public delete(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### patch



```php
public patch(string $pattern, \Closure $handler, ?string $name = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$name` | **?string** |  |





***

### middleware



```php
public middleware(\vosaka\http\middleware\MiddlewareInterface|\Closure $middleware): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middleware` | **\vosaka\http\middleware\MiddlewareInterface&#124;\Closure** |  |





***

### group



```php
public group(string $prefix, \Closure $callback, array $middleware = []): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$prefix` | **string** |  |
| `$callback` | **\Closure** |  |
| `$middleware` | **array** |  |





***


***
> Automatically generated on 2025-07-24
