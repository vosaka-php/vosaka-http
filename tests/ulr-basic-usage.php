<?php

require_once "../vendor/autoload.php";

use venndev\vosaka\VOsaka;
use vosaka\http\Browzr;
use vosaka\http\server\Router;
use vosaka\http\middleware\CorsMiddleware;
use vosaka\http\middleware\RateLimitMiddleware;

/**
 * VOsaka HTTP Library - Basic Usage Examples
 *
 * This file demonstrates the basic usage of the VOsaka HTTP library
 * including both client and server functionality.
 */

// Example 1: HTTP Client Usage
function httpClientExample(): Generator
{
    echo "=== HTTP Client Examples ===\n";

    try {
        // Simple GET request
        echo "Making GET request to httpbin.org...\n";
        $response = yield from Browzr::get("https://httpbin.org/get")->unwrap();
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Content-Type: " . $response->getHeaderLine("Content-Type") . "\n";
        echo "Body: " .
            substr($response->getBody()->getContents(), 0, 100) .
            "...\n\n";

        // POST request with JSON data
        echo "Making POST request with JSON data...\n";
        $postData = ["name" => "VOsaka", "type" => "HTTP Library"];
        $response = yield from Browzr::post(
            "https://httpbin.org/post",
            $postData,
            ["Content-Type" => "application/json"]
        )->unwrap();
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Response body snippet: " .
            substr($response->getBody()->getContents(), 0, 200) .
            "...\n\n";

        // Custom client with options
        echo "Using custom client with timeout...\n";
        $client = Browzr::client([
            "timeout" => 10,
            "user_agent" => "VOsaka-Example/1.0",
        ]);
        $response = yield from $client
            ->get("https://httpbin.org/delay/2")
            ->unwrap();
        echo "Status: " . $response->getStatusCode() . "\n\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

// Example 2: Async Request Processing
function concurrentRequestsExample(): Generator
{
    echo "=== Concurrent Requests Example ===\n";

    try {
        // Make multiple requests concurrently
        $requests = [
            Browzr::get("https://httpbin.org/delay/1"),
            Browzr::get("https://httpbin.org/delay/2"),
            Browzr::get("https://httpbin.org/delay/3"),
        ];

        echo "Making 3 concurrent requests...\n";
        $startTime = microtime(true);

        // Wait for all requests to complete
        $responses = yield from VOsaka::join(...$requests)->unwrap();

        $endTime = microtime(true);
        $totalTime = round($endTime - $startTime, 2);

        echo "All requests completed in {$totalTime} seconds\n";
        echo "Response status codes: ";
        foreach ($responses as $response) {
            echo $response->getStatusCode() . " ";
        }
        echo "\n\n";

        // Race requests (first one wins)
        echo "Racing requests (first to complete wins)...\n";
        $raceRequests = [
            Browzr::get("https://httpbin.org/delay/1"),
            Browzr::get("https://httpbin.org/delay/2"),
            Browzr::get("https://httpbin.org/delay/5"),
        ];

        $startTime = microtime(true);
        [$index, $response] = yield from VOsaka::select(
            ...$raceRequests
        )->unwrap();
        $endTime = microtime(true);
        $totalTime = round($endTime - $startTime, 2);

        echo "Request {$index} won in {$totalTime} seconds with status: " .
            $response->getStatusCode() .
            "\n\n";
    } catch (Exception $e) {
        echo "Error in concurrent requests: " . $e->getMessage() . "\n\n";
    }
}

// Main execution function
function main(): Generator
{
    echo "VOsaka HTTP Library - Basic Usage Examples\n";
    echo "=========================================\n\n";

    // Run client examples
    yield from httpClientExample();

    // Run concurrent requests example
    yield from concurrentRequestsExample();

    echo "Examples completed!\n";
}

// Check if this script is run directly
if (php_sapi_name() === "cli") {
    // Run the main function using VOsaka
    VOsaka::spawn(main());
    VOsaka::run();
} else {
    echo "This example should be run from the command line.\n";
    echo "Usage: php examples/basic-usage.php\n";
}
