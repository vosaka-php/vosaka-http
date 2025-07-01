***

# HttpResponseWriter





* Full name: `\vosaka\http\server\HttpResponseWriter`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### sendResponse



```php
public sendResponse(\venndev\vosaka\net\tcp\TCPStream $client, \Psr\Http\Message\ResponseInterface $response): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$client` | **\venndev\vosaka\net\tcp\TCPStream** |  |
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***

### buildResponseHeaders



```php
private buildResponseHeaders(\Psr\Http\Message\ResponseInterface $response): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***

### getResponseBody



```php
private getResponseBody(\Psr\Http\Message\ResponseInterface $response): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***

### shouldKeepAlive



```php
public shouldKeepAlive(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** |  |
| `$response` | **\Psr\Http\Message\ResponseInterface** |  |





***


***
> Automatically generated on 2025-07-01
