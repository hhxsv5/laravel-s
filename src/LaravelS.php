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
 * Laravel Request => Laravel handle => Laravel Response
 * Laravel Response => Swoole Response
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

        // file_put_contents('laravels.log', 'Laravels:onWorkerStart:start already included files ' . json_encode(get_included_files(), JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);

        // To implement gracefully reload
        // Delay to create Laravel
        // Delay to include Laravel's autoload.php
        $this->laravel = new Laravel($this->laravelConf);
        $this->laravel->prepareLaravel();
        $this->laravel->bindSwoole($this->swoole);

        // file_put_contents('laravels.log', 'Laravels:onWorkerStart:end already included files ' . json_encode(get_included_files(), JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        try {
            parent::onRequest($request, $response);
            $laravelRequest = (new Request($request))->toIlluminateRequest();
            $this->laravel->fireEvent('laravels.received_request', [$laravelRequest]);
            $success = $this->handleStaticResource($laravelRequest, $response);
            if ($success === false) {
                $this->handleDynamicResource($laravelRequest, $response);
            }
        } catch (\Exception $e) {
            echo sprintf('[%s][ERROR][LaravelS]onRequest: %s:%s, [%d]%s%s%s', date('Y-m-d H:i:s'), $e->getFile(), $e->getLine(), $e->getCode(), $e->getMessage(), PHP_EOL, $e->getTraceAsString()), PHP_EOL;
            $response->status(500);
            $response->end('Oops! An unexpected error occurred, please take a look the Swoole log.');
        }
    }

    protected function handleStaticResource(IlluminateRequest $laravelRequest, \swoole_http_response $swooleResponse)
    {
        // For Swoole < 1.9.17
        if (!empty($this->conf['handle_static'])) {
            $laravelResponse = $this->laravel->handleStatic($laravelRequest);
            if ($laravelResponse !== false) {
                $laravelResponse->headers->set('Server', $this->conf['server'], true);
                $this->laravel->fireEvent('laravels.generated_response', [$laravelRequest, $laravelResponse]);
                (new StaticResponse($swooleResponse, $laravelResponse))->send($this->conf['enable_gzip']);
                return true;
            }
        }
        return false;
    }

    protected function handleDynamicResource(IlluminateRequest $laravelRequest, \swoole_http_response $swooleResponse)
    {
        $laravelResponse = $this->laravel->handleDynamic($laravelRequest);
        $laravelResponse->headers->set('Server', $this->conf['server'], true);
        $this->laravel->fireEvent('laravels.generated_response', [$laravelRequest, $laravelResponse]);
        $this->laravel->cleanRequest($laravelRequest);
        (new DynamicResponse($swooleResponse, $laravelResponse))->send($this->conf['enable_gzip']);
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