***

# MiddlewareStack

Middleware stack for composing middleware layers



* Full name: `\vosaka\http\middleware\MiddlewareStack`



## Properties


### middleware



```php
private array $middleware
```






***

## Methods


### push



```php
public push(\vosaka\http\middleware\MiddlewareInterface|\Closure $middleware): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$middleware` | **\vosaka\http\middleware\MiddlewareInterface&#124;\Closure** |  |





***

### build



```php
public build(\Closure $finalHandler): \Closure
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$finalHandler` | **\Closure** |  |





***


***
> Automatically generated on 2025-07-24
