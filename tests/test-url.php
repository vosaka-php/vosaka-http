<?php

require_once "../vendor/autoload.php";

use venndev\vosaka\VOsaka;
use vosaka\http\Browzr;
use vosaka\http\message\Response;

$time = microtime(true);
function main(): Generator
{
    /**
     * @var Response $response
     */
    $response = yield from Browzr::get("https://jsonplaceholder.typicode.com/posts/1")->unwrap();
    var_dump($response->getBody()->getContents());
}

VOsaka::spawn(main());
VOsaka::run();

$time = microtime(true) - $time;
echo "" . $time . "";
