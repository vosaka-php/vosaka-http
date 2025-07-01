***

# Uri





* Full name: `\vosaka\http\message\Uri`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\Psr\Http\Message\UriInterface`](../../../Psr/Http/Message/UriInterface.md)
* This class is a **Final class**



## Properties


### scheme



```php
private string $scheme
```






***

### userInfo



```php
private string $userInfo
```






***

### host



```php
private string $host
```






***

### port



```php
private int|null $port
```






***

### path



```php
private string $path
```






***

### query



```php
private string $query
```






***

### fragment



```php
private string $fragment
```






***

## Methods


### __construct



```php
public __construct(string $uri): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uri` | **string** |  |





***

### getScheme



```php
public getScheme(): string
```












***

### getAuthority



```php
public getAuthority(): string
```












***

### getUserInfo



```php
public getUserInfo(): string
```












***

### getHost



```php
public getHost(): string
```












***

### getPort



```php
public getPort(): ?int
```












***

### getPath



```php
public getPath(): string
```












***

### getQuery



```php
public getQuery(): string
```












***

### getFragment



```php
public getFragment(): string
```












***

### withScheme



```php
public withScheme(string $scheme): \Psr\Http\Message\UriInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$scheme` | **string** |  |





***

### withUserInfo



```php
public withUserInfo(string $user, string|null $password = null): \Psr\Http\Message\UriInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$user` | **string** |  |
| `$password` | **string&#124;null** |  |





***

### withHost



```php
public withHost(string $host): \Psr\Http\Message\UriInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$host` | **string** |  |





***

### withPort



```php
public withPort(mixed $port): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$port` | **mixed** |  |





***

### withPath



```php
public withPath(string $path): \Psr\Http\Message\UriInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### withQuery



```php
public withQuery(string $query): \Psr\Http\Message\UriInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **string** |  |





***

### withFragment



```php
public withFragment(string $fragment): \Psr\Http\Message\UriInterface
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$fragment` | **string** |  |





***

### encode



```php
private encode(array|string $part, int $component): array|string|null
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$part` | **array&#124;string** |  |
| `$component` | **int** |  |





***

### resolve



```php
public static resolve(\Psr\Http\Message\UriInterface $base, \Psr\Http\Message\UriInterface $rel): \Psr\Http\Message\UriInterface
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$base` | **\Psr\Http\Message\UriInterface** |  |
| `$rel` | **\Psr\Http\Message\UriInterface** |  |





***

### removeDotSegments



```php
private static removeDotSegments(string $path): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### __toString



```php
public __toString(): string
```












***


***
> Automatically generated on 2025-07-01
