***

# UrlGenerator





* Full name: `\vosaka\http\utils\UrlGenerator`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### namedRoutes



```php
private array $namedRoutes
```






***

## Methods


### __construct



```php
public __construct(array $namedRoutes): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$namedRoutes` | **array** |  |





***

### generate



```php
public generate(string $name, array $params = [], array $query = []): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$params` | **array** |  |
| `$query` | **array** |  |





***

### buildUrl



```php
private buildUrl(string $pattern, array $params): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |
| `$params` | **array** |  |





***


***
> Automatically generated on 2025-07-01
