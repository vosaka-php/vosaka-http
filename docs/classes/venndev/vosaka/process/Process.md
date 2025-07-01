***

# Process





* Full name: `\venndev\vosaka\process\Process`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### running



```php
private bool $running
```






***

### stopped



```php
private bool $stopped
```






***

### signaled



```php
private bool $signaled
```






***

### exitCode



```php
private ?int $exitCode
```






***

### pid



```php
private ?int $pid
```






***

### process



```php
private mixed $process
```






***

### pipes



```php
private array $pipes
```






***

## Methods


### __construct



```php
public __construct(): mixed
```












***

### start



```php
public start(string $cmd, array $descriptorSpec): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$cmd` | **string** |  |
| `$descriptorSpec` | **array** |  |





***

### prepareCommand



```php
private prepareCommand(string $cmd): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$cmd` | **string** |  |





***

### handle

Start the process and handle its output asynchronously

```php
public handle(): \venndev\vosaka\core\Result&lt;string&gt;
```












***

### collectRemainingOutput



```php
private collectRemainingOutput(string& $output, string& $error): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** |  |
| `$error` | **string** |  |





***

### updateStatus



```php
private updateStatus(): void
```












***

### terminateProcess

Terminate the process forcefully

```php
private terminateProcess(): \venndev\vosaka\core\Result&lt;void&gt;
```












***

### stop

Stop the process gracefully

```php
public stop(): \venndev\vosaka\core\Result&lt;void&gt;
```












***

### getPid



```php
public getPid(): ?int
```












***

### getExitCode



```php
public getExitCode(): ?int
```












***

### isRunning



```php
public isRunning(): bool
```












***


***
> Automatically generated on 2025-07-01
