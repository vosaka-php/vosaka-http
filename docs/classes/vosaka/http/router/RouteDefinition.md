***

# RouteDefinition

Route definition data class



* Full name: `\vosaka\http\router\RouteDefinition`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### method



```php
public string $method
```






***

### pattern



```php
public string $pattern
```






***

### handler



```php
public \Closure $handler
```






***

### middleware



```php
public array $middleware
```






***

### compiled



```php
public \vosaka\http\router\CompiledRoute $compiled
```






***

### name



```php
public ?string $name
```






***

## Methods


### __construct



```php
public __construct(string $method, string $pattern, \Closure $handler, array $middleware, \vosaka\http\router\CompiledRoute $compiled, ?string $name = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |
| `$pattern` | **string** |  |
| `$handler` | **\Closure** |  |
| `$middleware` | **array** |  |
| `$compiled` | **\vosaka\http\router\CompiledRoute** |  |
| `$name` | **?string** |  |





***


***
> Automatically generated on 2025-07-24
