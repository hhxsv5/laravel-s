<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Laravel\Laravel;

class HttpServer
{
    protected $sw;

    public function __construct(array $svrConf = [])
    {
        $ip = isset($svrConf['ip']) ? $svrConf['ip'] : '0.0.0.0';
        $port = isset($svrConf['port']) ? $svrConf['port'] : 8841;

        $this->sw = new \swoole_http_server($ip, $port);
    }

    public function run(Laravel &$laravel)
    {
        $this->sw->on('request', function (\swoole_http_request $request, \swoole_http_response $response) use ($laravel) {
            $swooleRequest = new SwooleRequest($request);
            $laravelResponse = $laravel->handle($swooleRequest->toLaravelRequest());
            $swooleResponse = new SwooleResponse($response, $laravelResponse);
            $swooleResponse->send();
        });

        $this->sw->start();
    }

}