***

# MiddlewareInterface

Interface for HTTP middleware components.

Middleware components can process requests before they reach the handler
and/or process responses before they are sent to the client.

* Full name: `\vosaka\http\middleware\MiddlewareInterface`



## Methods


### process

Process an incoming server request.

```php
public process(\Psr\Http\Message\ServerRequestInterface $request, callable $next): \Psr\Http\Message\ResponseInterface|null
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | **\Psr\Http\Message\ServerRequestInterface** | The request to process |
| `$next` | **callable** | The next middleware/handler in the chain |


**Return Value:**

Return a response to short-circuit, or null to continue




***


***
> Automatically generated on 2025-07-24
