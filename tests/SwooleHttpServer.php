<?php

namespace Hhxsv5\LaravelS\Tests;

class SwooleHttpServer
{
    protected $sw;

    public function __construct($ip = '0.0.0.0', $port = 8011)
    {
        $this->sw = new \swoole_http_server($ip, $port, \SWOOLE_PROCESS);
        $this->sw->set([
            'dispatch_mode' => 2,
            'daemonize'     => 0,
        ]);
    }

    /**
     * @param callable $handler (\swoole_http_server $server, $workerId)
     */
    public function setWorkerStartHandler(callable $handler)
    {
        $this->sw->on('WorkerStart', $handler);
    }

    /**
     * @param callable $handler (\swoole_http_request $request, \swoole_http_response $response)
     */
    public function setRequestHandler(callable $handler)
    {
        $this->sw->on('Request', $handler);
    }

    public function start()
    {
        $this->sw->start();
    }
}
