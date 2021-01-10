<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Illuminate\LaravelTrait;
use Hhxsv5\LaravelS\Illuminate\LogTrait;
use Hhxsv5\LaravelS\Swoole\DynamicResponse;
use Hhxsv5\LaravelS\Swoole\Events\ServerStartInterface;
use Hhxsv5\LaravelS\Swoole\Events\ServerStopInterface;
use Hhxsv5\LaravelS\Swoole\Events\WorkerErrorInterface;
use Hhxsv5\LaravelS\Swoole\Events\WorkerStartInterface;
use Hhxsv5\LaravelS\Swoole\Events\WorkerStopInterface;
use Hhxsv5\LaravelS\Swoole\InotifyTrait;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessTrait;
use Hhxsv5\LaravelS\Swoole\Process\ProcessTitleTrait;
use Hhxsv5\LaravelS\Swoole\Request;
use Hhxsv5\LaravelS\Swoole\Server;
use Hhxsv5\LaravelS\Swoole\StaticResponse;
use Hhxsv5\LaravelS\Swoole\Timer\TimerTrait;
use Illuminate\Http\Request as IlluminateRequest;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server as HttpServer;
use Swoole\Server\Port;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * Swoole Request => Laravel Request
 * Laravel Request => Laravel handle => Laravel Response
 * Laravel Response => Swoole Response
 */
class LaravelS extends Server
{
    /**
     * Fix conflicts of traits
     */
    use InotifyTrait, LaravelTrait, LogTrait, ProcessTitleTrait, TimerTrait, CustomProcessTrait;

    /**@var array */
    protected $laravelConf;

    /**@var Laravel */
    protected $laravel;

    public function __construct(array $svrConf, array $laravelConf)
    {
        parent::__construct($svrConf);
        $this->laravelConf = $laravelConf;

        $timerConf = isset($this->conf['timer']) ? $this->conf['timer'] : [];
        $timerConf['process_prefix'] = $svrConf['process_prefix'];
        $this->swoole->timerProcess = $this->addTimerProcess($this->swoole, $timerConf, $this->laravelConf);

        $inotifyConf = isset($this->conf['inotify_reload']) ? $this->conf['inotify_reload'] : [];
        if (!isset($inotifyConf['watch_path'])) {
            $inotifyConf['watch_path'] = $this->laravelConf['root_path'];
        }
        $inotifyConf['process_prefix'] = $svrConf['process_prefix'];
        $this->swoole->inotifyProcess = $this->addInotifyProcess($this->swoole, $inotifyConf, $this->laravelConf);

        $processes = isset($this->conf['processes']) ? $this->conf['processes'] : [];
        $this->swoole->customProcesses = $this->addCustomProcesses($this->swoole, $svrConf['process_prefix'], $processes, $this->laravelConf);

        // Fire ServerStart event
        if (isset($this->conf['event_handlers']['ServerStart'])) {
            Laravel::autoload($this->laravelConf['root_path']);
            $this->fireEvent('ServerStart', ServerStartInterface::class, [$this->swoole]);
        }
    }

    protected function beforeWebSocketHandShake(SwooleRequest $request)
    {
        // Start Laravel's lifetime, then support session ...middleware.
        $laravelRequest = $this->convertRequest($this->laravel, $request);
        $this->laravel->bindRequest($laravelRequest);
        $this->laravel->fireEvent('laravels.received_request', [$laravelRequest]);
        $this->laravel->cleanProviders();
        $laravelResponse = $this->laravel->handleDynamic($laravelRequest);
        $this->laravel->fireEvent('laravels.generated_response', [$laravelRequest, $laravelResponse]);
    }

    protected function afterWebSocketOpen(SwooleRequest $request)
    {
        // End Laravel's lifetime.
        $this->laravel->saveSession();
        $this->laravel->clean();
    }

    protected function triggerWebSocketEvent($event, array $params)
    {
        if ($event === 'onHandShake') {
            $this->beforeWebSocketHandShake($params[0]);
            $params[1]->header('Server', $this->conf['server']);
        }

        parent::triggerWebSocketEvent($event, $params);

        switch ($event) {
            case 'onHandShake':
                if (isset($params[1]->header['Sec-Websocket-Accept'])) {
                    // Successful handshake
                    parent::triggerWebSocketEvent('onOpen', [$this->swoole, $params[0]]);
                }
                $this->afterWebSocketOpen($params[0]);
                break;
            case 'onOpen':
                $this->afterWebSocketOpen($params[1]);
                break;
        }
    }

    protected function triggerPortEvent(Port $port, $handlerClass, $event, array $params)
    {
        switch ($event) {
            case 'onHandShake':
                $this->beforeWebSocketHandShake($params[0]);
            case 'onRequest':
                $params[1]->header('Server', $this->conf['server']);
                break;
        }

        parent::triggerPortEvent($port, $handlerClass, $event, $params);

        switch ($event) {
            case 'onHandShake':
                if (isset($params[1]->header['Sec-Websocket-Accept'])) {
                    // Successful handshake
                    parent::triggerPortEvent($port, $handlerClass, 'onOpen', [$this->swoole, $params[0]]);
                }
                $this->afterWebSocketOpen($params[0]);
                break;
            case 'onOpen':
                $this->afterWebSocketOpen($params[1]);
                break;
        }
    }

    public function onShutdown(HttpServer $server)
    {
        parent::onShutdown($server);

        // Fire ServerStop event
        if (isset($this->conf['event_handlers']['ServerStop'])) {
            $this->laravel = $this->initLaravel($this->laravelConf, $this->swoole);
            $this->fireEvent('ServerStop', ServerStopInterface::class, [$server]);
        }
    }

    public function onWorkerStart(HttpServer $server, $workerId)
    {
        parent::onWorkerStart($server, $workerId);

        // To implement gracefully reload
        // Delay to create Laravel
        // Delay to include Laravel's autoload.php
        $this->laravel = $this->initLaravel($this->laravelConf, $this->swoole);

        // Fire WorkerStart event
        $this->fireEvent('WorkerStart', WorkerStartInterface::class, func_get_args());
    }

    public function onWorkerStop(HttpServer $server, $workerId)
    {
        parent::onWorkerStop($server, $workerId);

        // Fire WorkerStop event
        $this->fireEvent('WorkerStop', WorkerStopInterface::class, func_get_args());
    }

    public function onWorkerError(HttpServer $server, $workerId, $workerPId, $exitCode, $signal)
    {
        parent::onWorkerError($server, $workerId, $workerPId, $exitCode, $signal);

        Laravel::autoload($this->laravelConf['root_path']);

        // Fire WorkerError event
        $this->fireEvent('WorkerError', WorkerErrorInterface::class, func_get_args());
    }

    protected function convertRequest(Laravel $laravel, SwooleRequest $request)
    {
        $rawGlobals = $laravel->getRawGlobals();
        return (new Request($request))->toIlluminateRequest($rawGlobals['_SERVER'], $rawGlobals['_ENV']);
    }

    public function onRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse)
    {
        try {
            parent::onRequest($swooleRequest, $swooleResponse);
            $laravelRequest = $this->convertRequest($this->laravel, $swooleRequest);
            $this->laravel->bindRequest($laravelRequest);
            $this->laravel->fireEvent('laravels.received_request', [$laravelRequest]);
            $handleStaticSuccess = false;
            if ($this->conf['handle_static']) {
                // For Swoole < 1.9.17
                $handleStaticSuccess = $this->handleStaticResource($this->laravel, $laravelRequest, $swooleResponse);
            }
            if (!$handleStaticSuccess) {
                $this->handleDynamicResource($this->laravel, $laravelRequest, $swooleResponse);
            }
        } catch (\Exception $e) {
            $this->handleException($e, $swooleResponse);
        }
    }

    /**
     * @param \Exception $e
     * @param SwooleResponse $response
     */
    protected function handleException($e, SwooleResponse $response)
    {
        $error = sprintf(
            'onRequest: Uncaught exception "%s"([%d]%s) at %s:%s, %s%s',
            get_class($e),
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            PHP_EOL,
            $e->getTraceAsString()
        );
        $this->error($error);
        try {
            $response->status(500);
            $response->end('Oops! An unexpected error occurred');
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    protected function handleStaticResource(Laravel $laravel, IlluminateRequest $laravelRequest, SwooleResponse $swooleResponse)
    {
        $laravelResponse = $laravel->handleStatic($laravelRequest);
        if ($laravelResponse === false) {
            return false;
        }
        $laravelResponse->headers->set('Server', $this->conf['server'], true);
        $laravel->fireEvent('laravels.generated_response', [$laravelRequest, $laravelResponse]);
        $response = new StaticResponse($swooleResponse, $laravelResponse);
        $response->setChunkLimit($this->conf['swoole']['buffer_output_size']);
        $response->send($this->conf['enable_gzip']);
        return true;
    }

    protected function handleDynamicResource(Laravel $laravel, IlluminateRequest $laravelRequest, SwooleResponse $swooleResponse)
    {
        $laravel->cleanProviders();
        $laravelResponse = $laravel->handleDynamic($laravelRequest);
        $laravelResponse->headers->set('Server', $this->conf['server'], true);
        $laravel->fireEvent('laravels.generated_response', [$laravelRequest, $laravelResponse]);
        if ($laravelResponse instanceof BinaryFileResponse) {
            $response = new StaticResponse($swooleResponse, $laravelResponse);
        } else {
            $response = new DynamicResponse($swooleResponse, $laravelResponse);
        }
        $response->setChunkLimit($this->conf['swoole']['buffer_output_size']);
        $response->send($this->conf['enable_gzip']);
        $laravel->clean();
        return true;
    }

    /**@var OutputStyle */
    protected static $outputStyle;

    public static function setOutputStyle(OutputStyle $outputStyle)
    {
        static::$outputStyle = $outputStyle;
    }

    public static function getOutputStyle()
    {
        return static::$outputStyle;
    }
}
