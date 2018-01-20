<?php

namespace Hhxsv5\LaravelS\Swoole;

use Hhxsv5\LaravelS\Illuminate\Laravel;

class Server
{
    protected $sw;
    protected $laravel;

    public function __construct(array $svrConf = [], Laravel $laravel)
    {
        $ip = isset($svrConf['ip']) ? $svrConf['ip'] : '0.0.0.0';
        $port = isset($svrConf['port']) ? $svrConf['port'] : 8841;

        $settings = isset($svrConf['settings']) ? $svrConf['settings'] : [];

        if (isset($settings['ssl_cert_file'], $settings['ssl_key_file'])) {
            $this->sw = new \swoole_http_server($ip, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
        } else {
            $this->sw = new \swoole_http_server($ip, $port, SWOOLE_PROCESS);
        }

        $this->sw->set($settings);

        $this->laravel = $laravel;
    }

    protected function bind()
    {
        $this->sw->on('Start', [$this, 'onStart']);
        $this->sw->on('Shutdown', [$this, 'onShutdown']);
        $this->sw->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->sw->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->sw->on('WorkerExit', [$this, 'onWorkerExit']);
        $this->sw->on('WorkerError', [$this, 'onWorkerError']);
        $this->sw->on('request', [$this, 'onRequest']);
    }

    public function onStart(\swoole_http_server $server)
    {
        //save master pid
    }

    public function onShutdown(\swoole_http_server $server)
    {
        //remove master pid
    }

    public function onWorkerStart(\swoole_http_server $server, $workerId)
    {
        //todo: reload
        $this->laravel->prepareLaravel();
    }

    public function onWorkerStop(\swoole_http_server $server, $workerId)
    {

    }

    public function onWorkerExit(\swoole_http_server $server, $workerId)
    {

    }

    public function onWorkerError(\swoole_http_server $server, $workerId, $workerPid, $exitCode, $signal)
    {

    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $swooleRequest = new Request($request);
        $laravelResponse = $this->laravel->handle($swooleRequest->toIlluminateRequest());
        $swooleResponse = new Response($response, $laravelResponse);
        $swooleResponse->send();
    }

    public function run()
    {
        $this->bind();
        $this->sw->start();
    }

}