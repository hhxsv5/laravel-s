<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\Request;
use Hhxsv5\LaravelS\Swoole\Response;
use Hhxsv5\LaravelS\Swoole\Server;


/**
 * Swoole Request => Laravel Request
 * Laravel Request => Laravel handle => Laravel Response
 * Laravel Response => Swoole Request
 */
class LaravelS extends Server
{
    protected static $s;

    protected $laravelConf;
    protected $laravel;

    protected function __construct(array $svrConf = [], array $laravelConf)
    {
        parent::__construct($svrConf);
        $this->laravelConf = $laravelConf;
    }

    public function onWorkerStart(\swoole_http_server $server, $workerId)
    {
        parent::onWorkerStart($server, $workerId);

        //Delay to create Laravel Object to implements gracefully reload
        $this->laravel = new Laravel($this->laravelConf);
        $this->laravel->prepareLaravel();
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        parent::onRequest($request, $response);

        $swooleRequest = new Request($request);
        $laravelResponse = $this->laravel->handle($swooleRequest->toIlluminateRequest());
        $swooleResponse = new Response($response, $laravelResponse);
        $swooleResponse->send();
    }

    private function __clone()
    {

    }

    private function __sleep()
    {
        return [];
    }

    public function __wakeup()
    {
        self::$s = $this;
    }

    public function reload()
    {

    }

    public function __destruct()
    {

    }

    public static function getInstance(array $svrConf = [], array $laravelConf = [])
    {
        if (self::$s === null) {
            self::$s = new static($svrConf, $laravelConf);
        }
        return self::$s;
    }
}