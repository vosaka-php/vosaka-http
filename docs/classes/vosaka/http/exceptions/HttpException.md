***

# HttpException

HTTP Exception



* Full name: `\vosaka\http\exceptions\HttpException`
* Parent class: [`Exception`](../../../Exception.md)



## Properties


### statusCode



```php
private int $statusCode
```






***

## Methods


### __construct



```php
public __construct(string $message, int $statusCode = 500, ?\Throwable $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |
| `$statusCode` | **int** |  |
| `$previous` | **?\Throwable** |  |





***

### getStatusCode



```php
public getStatusCode(): int
```












***


***
> Automatically generated on 2025-07-24
