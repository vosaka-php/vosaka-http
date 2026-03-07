<?php

require_once "../vendor/autoload.php";

use vosaka\foroutines\AsyncMain;
use vosaka\foroutines\RunBlocking;
use vosaka\http\client\HttpClient;

#[AsyncMain]
function main(): void
{
    $lastTime = microtime(true);
    RunBlocking::new(function () {
        $httpClient = new HttpClient();
        $response = $httpClient->get("https://jsonplaceholder.typicode.com/posts/1")->await();
        var_dump($response->getBody()->getContents());
    });

    $elapsedTime = microtime(true) - $lastTime;
    echo "Elapsed time: " . $elapsedTime . " seconds\n";
}
