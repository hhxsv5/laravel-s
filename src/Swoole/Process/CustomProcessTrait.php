<?php

namespace Hhxsv5\LaravelS\Swoole\Process;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Swoole\Http\Server;
use Swoole\Process;

trait CustomProcessTrait
{
    public function addCustomProcesses(Server $swoole, $processPrefix, array $processes, array $laravelConfig)
    {
        $pidfile = dirname($swoole->setting['pid_file']) . '/laravels-custom-processes.pid';
        if (file_exists($pidfile)) {
            unlink($pidfile);
        }

        /**@var []CustomProcessInterface $processList */
        $processList = [];
        foreach ($processes as $item) {
            if (is_string($item)) {
                // Backwards compatible
                Laravel::autoload($laravelConfig['root_path']);
                $process = $item;
                $redirect = $process::isRedirectStdinStdout();
                $pipe = $process::getPipeType();
            } else {
                if (empty($item['class'])) {
                    throw new \InvalidArgumentException(sprintf(
                            'process class name must be specified'
                        )
                    );
                }
                if (isset($item['enable']) && !$item['enable']) {
                    continue;
                }
                $process = $item['class'];
                $redirect = isset($item['redirect']) ? $item['redirect'] : false;
                $pipe = isset($item['pipe']) ? $item['pipe'] : 0;
            }

            $processHandler = function (Process $worker) use ($pidfile, $swoole, $processPrefix, $process, $laravelConfig) {
                file_put_contents($pidfile, $worker->pid . "\n", FILE_APPEND | LOCK_EX);
                $this->initLaravel($laravelConfig, $swoole);
                if (!isset(class_implements($process)[CustomProcessInterface::class])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '%s must implement the interface %s',
                            $process,
                            CustomProcessInterface::class
                        )
                    );
                }
                /**@var CustomProcessInterface $process */
                $name = $process::getName() ?: 'custom';
                $this->setProcessTitle(sprintf('%s laravels: %s process', $processPrefix, $name));

                Process::signal(SIGUSR1, function ($signo) use ($name, $process, $worker, $pidfile, $swoole) {
                    $this->info(sprintf('Reloading %s process[pid=%d].', $name, $worker->pid));
                    $process::onReload($swoole, $worker);
                });

                $coroutineAvailable = class_exists('Swoole\Coroutine');
                $coroutineRuntimeAvailable = class_exists('Swoole\Runtime');
                $runProcess = function () use ($name, $process, $swoole, $worker, $coroutineAvailable, $coroutineRuntimeAvailable) {
                    $coroutineRuntimeAvailable && \Swoole\Runtime::enableCoroutine();
                    $this->callWithCatchException([$process, 'callback'], [$swoole, $worker]);
                    // Avoid frequent process creation
                    if ($coroutineAvailable) {
                        \Swoole\Coroutine::sleep(3);
                        swoole_event_exit();
                    } else {
                        sleep(3);
                    }
                };
                $coroutineAvailable ? go($runProcess) : $runProcess();
            };
            $customProcess = new Process(
                $processHandler,
                $redirect,
                $pipe
            );
            if ($swoole->addProcess($customProcess)) {
                $processList[] = $customProcess;
            }
        }
        return $processList;
    }
}
