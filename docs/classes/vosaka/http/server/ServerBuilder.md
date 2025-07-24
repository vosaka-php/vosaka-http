***

# ServerBuilder

Server builder for fluent configuration



* Full name: `\vosaka\http\server\ServerBuilder`



## Properties


### server



```php
private \vosaka\http\server\HttpServer $server
```






***

### address



```php
private string $address
```






***

## Methods


### __construct



```php
public __construct(\vosaka\http\server\HttpServer $server, string $address): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$server` | **\vosaka\http\server\HttpServer** |  |
| `$address` | **string** |  |





***

### serve



```php
public serve(): \venndev\vosaka\core\Result
```












***

### with_config



```php
public with_config(\vosaka\http\server\Router $router, \vosaka\http\server\ServerConfig $config): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$router` | **\vosaka\http\server\Router** |  |
| `$config` | **\vosaka\http\server\ServerConfig** |  |





***


***
> Automatically generated on 2025-07-24
