
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
| [`HttpClient`](./classes/vosaka/http/client/HttpClient.md) | Asynchronous HTTP Client using cURL Multi with VOsaka runtime.|




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
| [`ResponseConverter`](./classes/vosaka/http/message/ResponseConverter.md) | |
| [`ServerRequest`](./classes/vosaka/http/message/ServerRequest.md) | PSR-7 ServerRequest implementation for HTTP messages.|
| [`Stream`](./classes/vosaka/http/message/Stream.md) | PSR-7 Stream implementation for HTTP messages.|
| [`Uri`](./classes/vosaka/http/message/Uri.md) | |




### \vosaka\http\middleware

#### Classes

| Class | Description |
|-------|-------------|
| [`CorsMiddleware`](./classes/vosaka/http/middleware/CorsMiddleware.md) | CORS (Cross-Origin Resource Sharing) middleware.|
| [`FaviconMiddleware`](./classes/vosaka/http/middleware/FaviconMiddleware.md) | Favicon middleware.|
| [`MiddlewareStack`](./classes/vosaka/http/middleware/MiddlewareStack.md) | Middleware stack for composing middleware layers|
| [`RateLimitMiddleware`](./classes/vosaka/http/middleware/RateLimitMiddleware.md) | Rate limiting middleware for request throttling.|



#### Interfaces

| Interface | Description |
|-----------|-------------|
| [`MiddlewareInterface`](./classes/vosaka/http/middleware/MiddlewareInterface.md) | Interface for HTTP middleware components.|



### \vosaka\http\router

#### Classes

| Class | Description |
|-------|-------------|
| [`CompiledRoute`](./classes/vosaka/http/router/CompiledRoute.md) | Compiled route pattern for efficient matching|
| [`PatternCompiler`](./classes/vosaka/http/router/PatternCompiler.md) | |
| [`Route`](./classes/vosaka/http/router/Route.md) | |
| [`RouteDefinition`](./classes/vosaka/http/router/RouteDefinition.md) | Route definition data class|
| [`RouteGroup`](./classes/vosaka/http/router/RouteGroup.md) | Route group helper for fluent API|
| [`RouteMatch`](./classes/vosaka/http/router/RouteMatch.md) | Route match result|
| [`RouteMatcher`](./classes/vosaka/http/router/RouteMatcher.md) | |
| [`Router`](./classes/vosaka/http/router/Router.md) | Enhanced Router with comprehensive parameter support<br />Now refactored into smaller, focused classes|




### \vosaka\http\server

#### Classes

| Class | Description |
|-------|-------------|
| [`ErrorHandlerManager`](./classes/vosaka/http/server/ErrorHandlerManager.md) | |
| [`HttpRequestParser`](./classes/vosaka/http/server/HttpRequestParser.md) | |
| [`HttpResponseWriter`](./classes/vosaka/http/server/HttpResponseWriter.md) | |
| [`HttpServer`](./classes/vosaka/http/server/HttpServer.md) | |
| [`RequestProcessor`](./classes/vosaka/http/server/RequestProcessor.md) | |
| [`ServerBuilder`](./classes/vosaka/http/server/ServerBuilder.md) | Server builder for fluent configuration|
| [`ServerConfig`](./classes/vosaka/http/server/ServerConfig.md) | Server configuration|
| [`ServerDebugHelper`](./classes/vosaka/http/server/ServerDebugHelper.md) | |




### \vosaka\http\utils

#### Classes

| Class | Description |
|-------|-------------|
| [`HttpUtils`](./classes/vosaka/http/utils/HttpUtils.md) | HTTP utility functions for common tasks.|
| [`UrlGenerator`](./classes/vosaka/http/utils/UrlGenerator.md) | |




***
> Automatically generated on 2025-07-24
