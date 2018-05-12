<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\DynamicResponse;
use Hhxsv5\LaravelS\Swoole\Request;
use Hhxsv5\LaravelS\Swoole\Server;
use Hhxsv5\LaravelS\Swoole\StaticResponse;
use Hhxsv5\LaravelS\Swoole\Traits\InotifyTrait;
use Hhxsv5\LaravelS\Swoole\Traits\TimerTrait;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * Swoole Request => Laravel Request
 * Laravel Request => Laravel handle => Laravel Response
 * Laravel Response => Swoole Response
 */
class LaravelS extends Server
{
    use TimerTrait, InotifyTrait {
        TimerTrait::setProcessTitle insteadof InotifyTrait;
    }

    protected $laravelConf;

    /**
     * @var Laravel $laravel
     */
    protected $laravel;

    public function __construct(array $svrConf, array $laravelConf)
    {
        parent::__construct($svrConf);
        $this->laravelConf = $laravelConf;

        $timerCfg = isset($this->conf['timer']) ? $this->conf['timer'] : [];
        $timerCfg['process_prefix'] = $svrConf['process_prefix'];
        $this->addTimerProcess($this->swoole, $timerCfg);

        $inotifyCfg = isset($this->conf['inotify_reload']) ? $this->conf['inotify_reload'] : [];
        $inotifyCfg['root_path'] = $this->laravelConf['root_path'];
        $inotifyCfg['process_prefix'] = $svrConf['process_prefix'];
        $this->addInotifyProcess($this->swoole, $inotifyCfg);
    }

    protected function initLaravel()
    {
        $laravel = new Laravel($this->laravelConf);
        $laravel->prepareLaravel();
        $laravel->bindSwoole($this->swoole);
        return $laravel;
    }

    public function onWorkerStart(\swoole_http_server $server, $workerId)
    {
        parent::onWorkerStart($server, $workerId);

        // To implement gracefully reload
        // Delay to create Laravel
        // Delay to include Laravel's autoload.php
        $this->laravel = $this->initLaravel();
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        try {
            $rawGlobals = $this->laravel->getRawGlobals();
            $server = isset($rawGlobals['_SERVER']) ? $rawGlobals['_SERVER'] : [];
            $env = isset($rawGlobals['_ENV']) ? $rawGlobals['_ENV'] : [];
            $laravelRequest = (new Request($request))->toIlluminateRequest($server, $env);
            $this->laravel->bindRequest($laravelRequest);
            $this->laravel->fireEvent('laravels.received_request', [$laravelRequest]);
            $success = $this->handleStaticResource($laravelRequest, $response);
            if ($success === false) {
                $this->handleDynamicResource($laravelRequest, $response);
            }
        } catch (\Exception $e) {
            $this->handleException($e, $response);
        } catch (\Throwable $e) {
            $this->handleException($e, $response);
        }
    }

    /**
     * @param \Exception|\Throwable $e
     * @param \swoole_http_response $response
     */
    protected function handleException($e, \swoole_http_response $response)
    {
        $error = sprintf('onRequest: Uncaught exception "%s"([%d]%s) at %s:%s, %s%s', get_class($e), $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), PHP_EOL, $e->getTraceAsString());
        $this->log($error, 'ERROR');
        try {
            $response->status(500);
            $response->end('Oops! An unexpected error occurred: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Catch: zm_deactivate_swoole: Fatal error: Uncaught exception 'ErrorException' with message 'swoole_http_response::status(): http client#2 is not exist.
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
        if ($laravelResponse instanceof BinaryFileResponse) {
            (new StaticResponse($swooleResponse, $laravelResponse))->send($this->conf['enable_gzip']);
        } else {
            (new DynamicResponse($swooleResponse, $laravelResponse))->send($this->conf['enable_gzip']);
        }
        return true;
    }
}
