***

# PatternCompiler





* Full name: `\vosaka\http\router\PatternCompiler`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### cache



```php
private array $cache
```






***

## Methods


### compile



```php
public compile(string $pattern): \vosaka\http\router\CompiledRoute
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |





***

### parseSegments



```php
private parseSegments(string $regex, string $originalPattern): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$regex` | **string** |  |
| `$originalPattern` | **string** |  |





***

### parseParameterDefinition



```php
private parseParameterDefinition(string $paramDef): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$paramDef` | **string** |  |





***

### buildRegexParts



```php
private buildRegexParts(array $segments, array& $params): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$segments` | **array** |  |
| `$params` | **array** |  |





***

### clearCache



```php
public clearCache(): void
```












***


***
> Automatically generated on 2025-07-01
