<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Swoole\Http\Server;
use Swoole\Process;

trait CustomProcessTrait
{
    use ProcessTitleTrait;
    use LogTrait;

    public function addCustomProcesses(Server $swoole, $processPrefix, array $processes, array $laravelConfig)
    {
        if (!empty($processes)) {
            Laravel::autoload($laravelConfig['root_path']);
        }

        /**@var []CustomProcessInterface $processList */
        $processList = [];
        foreach ($processes as $process) {
            if (!isset(class_implements($process)[CustomProcessInterface::class])) {
                throw new \InvalidArgumentException(sprintf(
                        '%s must implement the interface %s',
                        $process,
                        CustomProcessInterface::class
                    )
                );
            }
            $processHandler = function (Process $worker) use ($swoole, $processPrefix, $process, $laravelConfig) {
                $name = $process::getName() ?: 'custom';
                $this->setProcessTitle(sprintf('%s laravels: %s process', $processPrefix, $name));
                $this->initLaravel($laravelConfig, $swoole);

                Process::signal(SIGUSR1, function ($signo) use ($name, $process, $worker, $swoole) {
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
                $enableCoroutine ? go($runProcess) : $runProcess();;
            };
            $customProcess = new Process(
                $processHandler,
                $process::isRedirectStdinStdout(),
                $process::getPipeType()
            );
            if ($swoole->addProcess($customProcess)) {
                $processList[] = $customProcess;
            }
        }
        return $processList;
    }

}
