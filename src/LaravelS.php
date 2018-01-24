<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\DynamicResponse;
use Hhxsv5\LaravelS\Swoole\Request;
use Hhxsv5\LaravelS\Swoole\Server;
use Hhxsv5\LaravelS\Swoole\StaticResponse;
use Illuminate\Http\Request as IlluminateRequest;


/**
 * Swoole Request => Laravel Request
 * Laravel Request => Laravel handle => Laravel ResponseInterface
 * Laravel ResponseInterface => Swoole Request
 */
class LaravelS extends Server
{
    protected static $s;

    protected $laravelConf;
    /**
     * @var Laravel $laravel
     */
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

        $laravelRequest = (new Request($request))->toIlluminateRequest();
        $success = $this->handleStaticResource($laravelRequest, $response);
        if (!$success) {
            $this->handleDynamicResource($laravelRequest, $response);
        }

    }

    protected function handleStaticResource(IlluminateRequest $laravelRequest, \swoole_http_response $swooleResponse)
    {
        if (!empty($this->conf['handle_static'])) {
            $laravelResponse = $this->laravel->handleStatic($laravelRequest);
            if ($laravelResponse) {
                (new StaticResponse($swooleResponse, $laravelResponse))->send();
                return true;
            }
        }
        return false;
    }

    protected function handleDynamicResource(IlluminateRequest $laravelRequest, \swoole_http_response $swooleResponse)
    {
        $laravelResponse = $this->laravel->handleDynamic($laravelRequest);
        (new DynamicResponse($swooleResponse, $laravelResponse))->send();
        return true;
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