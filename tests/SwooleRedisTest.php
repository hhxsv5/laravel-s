<?php

namespace Hhxsv5\LaravelS\Tests;

use PHPUnit\Framework\TestCase;

class SwooleRedisTest extends TestCase
{
    public function testStart()
    {
        $server = new SwooleHttpServer();
        $server->setWorkerStartHandler(function (\swoole_http_server $server, $workerId) {
            $server->redis = new \Redis();
            $server->redis->connect('127.0.0.1', 6379);
        });
        $server->setRequestHandler(function (\swoole_http_request $request, \swoole_http_response $response) {
            $db = isset($request->get['db']) ? $request->get['db'] : null;
            if ($db !== null) {
                $request->server->redis->select($db);
            }
            $v = time();
            $request->server->redis->set('test', $v);
            $response->end('done: ' . $v);
        });
        $this->assertInstanceOf(SwooleHttpServer::class, $server);
        $server->start();
    }
}