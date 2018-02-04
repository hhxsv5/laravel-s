<?php

use PHPUnit\Framework\TestCase;

class SwooleHttpServer
{
    protected $redis;
    protected $sw;

    public function __construct()
    {
        $this->sw = new \swoole_http_server('127.0.0.1', 5200, \SWOOLE_PROCESS);
        $this->sw->set([
            'dispatch_mode' => 2,
            'daemonize'     => 0,
        ]);
        $this->sw->on('WorkerStart', function (\swoole_http_server $server, $workerId) {
            $this->redis = new \Redis();
            $this->redis->connect('127.0.0.1', 6379);
        });
        $this->sw->on('Request', function (\swoole_http_request $request, \swoole_http_response $response) {
            $db = isset($request->get['db']) ? $request->get['db'] : null;
            if ($db !== null) {
                $this->redis->select($db);
            }
            $v = time();
            $this->redis->set('test', $v);
            $response->end('done: ' . $v);
        });
    }

    public function start()
    {
        $this->sw->start();
    }
}

/**
 * @covers SwooleHttpServer
 */
final class SwooleRedisTest extends TestCase
{
    public function testStart()
    {
        $server = new SwooleHttpServer();
        $this->assertInstanceOf(SwooleHttpServer::class, $server);
        $server->start();
    }
}