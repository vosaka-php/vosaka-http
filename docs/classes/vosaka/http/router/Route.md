***

# Route





* Full name: `\vosaka\http\router\Route`



## Properties


### methods



```php
private array $methods
```






***

### handlers



```php
private array $handlers
```






***

### middleware



```php
private array $middleware
```






***

## Methods


### new



```php
public static new(): self
```



* This method is **static**.








***

### get



```php
public get(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### post



```php
public post(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### put



```php
public put(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### delete



```php
public delete(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### patch



```php
public patch(\Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handler` | **\Closure** |  |





***

### method



```php
public method(string $method, \Closure $handler): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | **string** |  |
| `$handler` | **\Closure** |  |





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

### handle



```php
public handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### supportsMethods



```php
public supportsMethods(): array
```












***


***
> Automatically generated on 2025-07-01
