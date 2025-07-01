
***

# Documentation



This is an automatically generated documentation for **Documentation**.


## Namespaces


### \vosaka\http

#### Classes

| Class | Description |
|-------|-------------|
| [`Browzr`](./classes/vosaka/http/Browzr.md) | VOsaka HTTP Library - Main Facade|




### \vosaka\http\client

#### Classes

| Class | Description |
|-------|-------------|
| [`HttpClient`](./classes/vosaka/http/client/HttpClient.md) | Asynchronous HTTP Client using VOsaka runtime.|




### \vosaka\http\exceptions

#### Classes

| Class | Description |
|-------|-------------|
| [`HttpException`](./classes/vosaka/http/exceptions/HttpException.md) | HTTP Exception|




### \vosaka\http\message

#### Classes

| Class | Description |
|-------|-------------|
| [`Request`](./classes/vosaka/http/message/Request.md) | PSR-7 Request implementation for HTTP messages.|
| [`Response`](./classes/vosaka/http/message/Response.md) | PSR-7 Response implementation for HTTP messages.|
| [`ServerRequest`](./classes/vosaka/http/message/ServerRequest.md) | PSR-7 ServerRequest implementation for HTTP messages.|
| [`Stream`](./classes/vosaka/http/message/Stream.md) | PSR-7 Stream implementation for HTTP messages.|
| [`Uri`](./classes/vosaka/http/message/Uri.md) | |




### \vosaka\http\middleware

#### Classes

| Class | Description |
|-------|-------------|
| [`CorsMiddleware`](./classes/vosaka/http/middleware/CorsMiddleware.md) | CORS (Cross-Origin Resource Sharing) middleware.|
| [`MiddlewareStack`](./classes/vosaka/http/middleware/MiddlewareStack.md) | Middleware stack for composing middleware layers|
| [`RateLimitMiddleware`](./classes/vosaka/http/middleware/RateLimitMiddleware.md) | Rate limiting middleware for request throttling.|



#### Interfaces

| Interface | Description |
|-----------|-------------|
| [`MiddlewareInterface`](./classes/vosaka/http/middleware/MiddlewareInterface.md) | Interface for HTTP middleware components.|



### \vosaka\http\server

#### Classes

| Class | Description |
|-------|-------------|
| [`CompiledRoute`](./classes/vosaka/http/server/CompiledRoute.md) | Compiled route pattern for efficient matching|
| [`HttpServer`](./classes/vosaka/http/server/HttpServer.md) | |
| [`Route`](./classes/vosaka/http/server/Route.md) | |
| [`RouteDefinition`](./classes/vosaka/http/server/RouteDefinition.md) | Route definition data class|
| [`RouteGroup`](./classes/vosaka/http/server/RouteGroup.md) | Route group helper for fluent API|
| [`RouteMatch`](./classes/vosaka/http/server/RouteMatch.md) | Route match result|
| [`Router`](./classes/vosaka/http/server/Router.md) | Enhanced Router with comprehensive parameter support|
| [`ServerBuilder`](./classes/vosaka/http/server/ServerBuilder.md) | Server builder for fluent configuration|
| [`ServerConfig`](./classes/vosaka/http/server/ServerConfig.md) | Server configuration|




### \vosaka\http\utils

#### Classes

| Class | Description |
|-------|-------------|
| [`HttpUtils`](./classes/vosaka/http/utils/HttpUtils.md) | HTTP utility functions for common tasks.|




***
> Automatically generated on 2025-07-01
