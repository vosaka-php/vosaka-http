***

# CompiledRoute

Compiled route pattern for efficient matching



* Full name: `\vosaka\http\router\CompiledRoute`



## Properties


### regex



```php
public string $regex
```






***

### params



```php
public array $params
```






***

### constraints



```php
public array $constraints
```






***

### hasWildcard



```php
public bool $hasWildcard
```






***

## Methods


### __construct



```php
public __construct(string $regex, array $params, array $constraints = [], bool $hasWildcard = false): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$regex` | **string** |  |
| `$params` | **array** |  |
| `$constraints` | **array** |  |
| `$hasWildcard` | **bool** |  |





***

### getParameterNames

Get parameter names

```php
public getParameterNames(): array
```












***

### hasParameter

Check if route has specific parameter

```php
public hasParameter(string $name): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |





***

### getRegex

Get regex pattern

```php
public getRegex(): string
```












***

### isWildcard

Check if this is a wildcard route

```php
public isWildcard(): bool
```












***

### debug

Debug: Show compiled pattern info

```php
public debug(): array
```












***


***
> Automatically generated on 2025-07-24
