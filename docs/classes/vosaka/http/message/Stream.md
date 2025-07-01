***

# Stream

PSR-7 Stream implementation for HTTP messages.

This class provides a stream implementation that can work with various
resource types including files, memory streams, and network streams.
It implements the PSR-7 StreamInterface for compatibility with HTTP
message standards.

* Full name: `\vosaka\http\message\Stream`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\Psr\Http\Message\StreamInterface`](../../../Psr/Http/Message/StreamInterface.md)
* This class is a **Final class**



## Properties


### resource



```php
private mixed $resource
```






***

### readable



```php
private bool $readable
```






***

### writable



```php
private bool $writable
```






***

### seekable



```php
private bool $seekable
```






***

### size



```php
private int|null $size
```






***

### uri



```php
private string|null $uri
```






***

### readWriteHash



```php
private static array&lt;string,array&lt;string,bool&gt;&gt; $readWriteHash
```



* This property is **static**.


***

## Methods


### __construct



```php
public __construct(mixed $stream): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **mixed** |  |





***

### __destruct



```php
public __destruct(): mixed
```












***

### __toString



```php
public __toString(): string
```












***

### close



```php
public close(): void
```












***

### detach



```php
public detach(): mixed
```












***

### getSize



```php
public getSize(): ?int
```












***

### tell



```php
public tell(): int
```












***

### eof



```php
public eof(): bool
```












***

### isSeekable



```php
public isSeekable(): bool
```












***

### seek



```php
public seek(int $offset, int $whence = SEEK_SET): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$offset` | **int** |  |
| `$whence` | **int** |  |





***

### rewind



```php
public rewind(): void
```












***

### isWritable



```php
public isWritable(): bool
```












***

### write



```php
public write(string $string): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$string` | **string** |  |





***

### isReadable



```php
public isReadable(): bool
```












***

### read



```php
public read(int $length): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$length` | **int** |  |





***

### getContents



```php
public getContents(): string
```












***

### getMetadata



```php
public getMetadata(?string $key = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **?string** |  |





***

### create

Create a new stream from a string.

```php
public static create(string $content = &quot;&quot;): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$content` | **string** |  |





***

### createFromFile

Create a new stream from a file.

```php
public static createFromFile(string $filename, string $mode = &quot;r&quot;): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string** |  |
| `$mode` | **string** |  |





***

### createFromResource

Create a new stream from a resource.

```php
public static createFromResource(mixed $resource): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$resource` | **mixed** |  |





***


***
> Automatically generated on 2025-07-01
