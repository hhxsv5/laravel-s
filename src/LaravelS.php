<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\DynamicResponse;
use Hhxsv5\LaravelS\Swoole\Request;
use Hhxsv5\LaravelS\Swoole\Server;
use Hhxsv5\LaravelS\Swoole\StaticResponse;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


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

    protected function __construct(array $svrConf, array $laravelConf)
    {
        parent::__construct($svrConf);
        $this->laravelConf = $laravelConf;
        $this->addInotifyProcess();
        $this->addTimerProcess();
    }

    protected function addInotifyProcess()
    {
        if (empty($this->conf['inotify_reload']['enable'])) {
            return;
        }

        if (!extension_loaded('inotify')) {
            $this->log('require extension inotify', 'WARN');
            return;
        }

        $log = !empty($this->conf['inotify_reload']['log']);
        $fileTypes = isset($this->conf['inotify_reload']['file_types']) ? (array)$this->conf['inotify_reload']['file_types'] : [];
        $autoReload = function () use ($fileTypes, $log) {
            $this->setProcessTitle(sprintf('%s laravels: inotify process', $this->conf['process_prefix']));
            $inotify = new Inotify($this->laravelConf['rootPath'], IN_CREATE | IN_MODIFY | IN_DELETE, function ($event) use ($log) {
                $this->swoole->reload();
                if ($log) {
                    $this->log(sprintf('reloaded by inotify, file: %s', $event['name']));
                }
            });
            $inotify->addFileTypes($fileTypes);
            $inotify->watch();
            if ($log) {
                $this->log(sprintf('count of watched files by inotify: %d', $inotify->getWatchedFileCount()));
            }
            $inotify->start();
        };

        $inotifyProcess = new \swoole_process($autoReload, false, false);
        $this->swoole->addProcess($inotifyProcess);
    }

    protected function addTimerProcess()
    {
        if (empty($this->conf['timer']['enable']) || empty($this->conf['timer']['jobs'])) {
            return;
        }

        $startTimer = function () {
            $this->setProcessTitle(sprintf('%s laravels: timer process', $this->conf['process_prefix']));
            $this->initLaravel();
            foreach ($this->conf['timer']['jobs'] as $jobClass) {
                $job = new $jobClass();
                if (!($job instanceof CronJob)) {
                    throw new \Exception(sprintf('%s must implement the abstract class %s', get_class($job), CronJob::class));
                }
                $timerId = swoole_timer_tick($job->interval(), function () use ($job) {
                    try {
                        $job->run();
                    } catch (\Exception $e) {
                        $this->logException($e);
                    }
                });
                $job->setTimerId($timerId);
            }
        };

        $timerProcess = new \swoole_process($startTimer, false, false);
        $this->swoole->addProcess($timerProcess);
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

    public static function getInstance(array $svrConf, array $laravelConf)
    {
        if (self::$s === null) {
            self::$s = new static($svrConf, $laravelConf);
        }
        return self::$s;
    }
}
