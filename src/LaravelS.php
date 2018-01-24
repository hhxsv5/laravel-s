<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\DynamicResponse;
use Hhxsv5\LaravelS\Swoole\Request;
use Hhxsv5\LaravelS\Swoole\Server;
use Hhxsv5\LaravelS\Swoole\StaticResponse;


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

        $success = $this->handleStaticResource($request, $response);
        if (!$success) {
            $this->handleDynamicResource($request, $response);
        }

    }

    protected function handleStaticResource(\swoole_http_request $request, \swoole_http_response $response)
    {
        if (!empty($this->conf['handle_static'])) {
            $laravelRequest = (new Request($request))->toIlluminateRequest();
            $laravelResponse = $this->laravel->handleStatic($laravelRequest);
            if ($laravelResponse) {
                (new StaticResponse($response, $laravelResponse))->send();
            }
        }
        return false;
    }

    protected function handleDynamicResource(\swoole_http_request $request, \swoole_http_response $response)
    {
        $laravelRequest = (new Request($request))->toIlluminateRequest();
        $laravelResponse = $this->laravel->handleDynamic($laravelRequest);
        (new DynamicResponse($response, $laravelResponse))->send();
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