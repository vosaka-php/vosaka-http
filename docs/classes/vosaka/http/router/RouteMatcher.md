***

# RouteMatcher





* Full name: `\vosaka\http\router\RouteMatcher`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### findMatch



```php
public findMatch(array $routes, \Psr\Http\Message\ServerRequestInterface $request): ?\vosaka\http\router\RouteMatch
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$routes` | **array** |  |
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |





***

### matchPattern



```php
private matchPattern(\vosaka\http\router\CompiledRoute $compiled, string $path): ?array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$compiled` | **\vosaka\http\router\CompiledRoute** |  |
| `$path` | **string** |  |





***


***
> Automatically generated on 2025-07-24
