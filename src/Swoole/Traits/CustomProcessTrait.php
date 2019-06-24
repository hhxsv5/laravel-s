<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
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
                $name = $process::getName() ?: 'custom';
                $this->setProcessTitle(sprintf('%s laravels: %s process', $processPrefix, $name));

                Process::signal(SIGUSR1, function ($signo) use ($name, $process, $worker, $pidfile, $swoole) {
                    $this->info(sprintf('Reloading the process %s [pid=%d].', $name, $worker->pid));
                    $process::onReload($swoole, $worker);
                });

                $enableCoroutine = class_exists('Swoole\Coroutine');
                $runProcess = function () use ($name, $process, $swoole, $worker, $enableCoroutine) {
                    $maxTry = 10;
                    $i = 0;
                    do {
                        $this->callWithCatchException([$process, 'callback'], [$swoole, $worker]);
                        ++$i;
                        if ($enableCoroutine) {
                            \Swoole\Coroutine::sleep(1);
                        } else {
                            sleep(1);
                        }
                    } while ($i < $maxTry);
                    $this->error(
                        sprintf(
                            'The custom process "%s" reaches the maximum number of retries %d times, and will be restarted by the manager process.',
                            $name,
                            $maxTry
                        )
                    );
                };
                $enableCoroutine ? go($runProcess) : $runProcess();
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
