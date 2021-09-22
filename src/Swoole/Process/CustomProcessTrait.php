<?php

namespace Hhxsv5\LaravelS\Swoole\Process;

use Swoole\Http\Server;
use Swoole\Process;

trait CustomProcessTrait
{
    private $customProcessPidFile = 'laravels-custom-processes.pid';

    public function addCustomProcesses(Server $swoole, $processPrefix, array $processes, array $laravelConfig)
    {
        $pidfile = dirname($swoole->setting['pid_file']) . '/' . $this->customProcessPidFile;
        if (file_exists($pidfile)) {
            unlink($pidfile);
        }

        /**@var []CustomProcessInterface $processList */
        $processList = [];
        foreach ($processes as $name => $item) {
            if (empty($item['class'])) {
                throw new \InvalidArgumentException(sprintf(
                        'process class name must be specified'
                    )
                );
            }
            if (isset($item['enable']) && !$item['enable']) {
                continue;
            }
            $processClass = $item['class'];
            $restartInterval = isset($item['restart_interval']) ? (int)$item['restart_interval'] : 5;
            $callback = function (Process $worker) use ($pidfile, $swoole, $processPrefix, $processClass, $restartInterval, $name, $laravelConfig) {
                file_put_contents($pidfile, $worker->pid . "\n", FILE_APPEND | LOCK_EX);
                $this->initLaravel($laravelConfig, $swoole);
                if (!isset(class_implements($processClass)[CustomProcessInterface::class])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '%s must implement the interface %s',
                            $processClass,
                            CustomProcessInterface::class
                        )
                    );
                }
                /**@var CustomProcessInterface $processClass */
                $this->setProcessTitle(sprintf('%s laravels: %s process', $processPrefix, $name));

                Process::signal(SIGUSR1, function ($signo) use ($name, $processClass, $worker, $pidfile, $swoole) {
                    $this->info(sprintf('Reloading %s process[PID=%d].', $name, $worker->pid));
                    $processClass::onReload($swoole, $worker);
                });

                if (method_exists($processClass, 'onStop')) {
                    Process::signal(SIGTERM, function ($signo) use ($name, $processClass, $worker, $pidfile, $swoole) {
                        $this->info(sprintf('Stopping %s process[PID=%d].', $name, $worker->pid));
                        $processClass::onStop($swoole, $worker);
                    });
                }

                if (class_exists('Swoole\Runtime')) {
                    \Swoole\Runtime::enableCoroutine();
                }

                $this->callWithCatchException([$processClass, 'callback'], [$swoole, $worker]);

                // Avoid frequent process creation
                if (class_exists('Swoole\Coroutine')) {
                    \Swoole\Coroutine::sleep($restartInterval);
                } else {
                    sleep($restartInterval);
                }
            };

            // for multiple processes
            if (isset($item['num']) && $item['num'] > 1) {
                for ($i = 0; $i < $item['num']; $i++) {
                    $process = $this->makeProcess($callback, $item);
                    $swoole->addProcess($process);
                    $processList[$name . $i] = $process;
                }

                return $processList;
            }

            // for single process
            $process = $this->makeProcess($callback, $item);
            $swoole->addProcess($process);
            $processList[$name] = $process;
        }
        return $processList;
    }

    public function makeProcess($callback, $item)
    {
        $redirect = isset($item['redirect']) ? $item['redirect'] : false;
        $pipe = isset($item['pipe']) ? $item['pipe'] : 0;
        $process = version_compare(SWOOLE_VERSION, '4.3.0', '>=')
            ? new Process($callback, $redirect, $pipe, class_exists('Swoole\Coroutine'))
            : new Process($callback, $redirect, $pipe);
        if (isset($item['queue'])) {
            if (empty($item['queue'])) {
                $process->useQueue();
            } else {
                $msgKey = isset($item['msg_key']) ? $item['msg_key'] : 0;
                $mode = isset($item['mode']) ? $item['mode'] : 2;
                $capacity = isset($item['capacity']) ? $item['capacity'] : -1;
                $process->useQueue($msgKey, $mode, $capacity);
            }
        }

        return $process;
    }
}
