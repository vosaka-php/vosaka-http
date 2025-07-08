<?php

use venndev\vosaka\VOsaka;
use vosaka\http\Browzr;
use vosaka\http\message\Response;

require_once "../vendor/autoload.php";

function main(): Generator
{
    /**
     * @var Response $response
     */
    $response = yield from Browzr::get("https://httpbin.org/get")->unwrap();
    var_dump($response->getBody()->getContents());
}

VOsaka::spawn(main());
VOsaka::run();
