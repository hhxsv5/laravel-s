<?php

namespace Hhxsv5\LaravelS\Tests;

use \PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    public function testGet()
    {
        $server = new SwooleHttpServer();
        $server->setRequestHandler(function (\swoole_http_request $request, \swoole_http_response $response) {
            $url = 'http://example.org';
            $cli = new \Hhxsv5\LaravelS\Clients\Coroutine\HTTP($url);
            $body = $cli->get();
            var_dump('body: ' . $body);
            $response->end('body: ' . $body);
        });
        $server->start();
    }
}