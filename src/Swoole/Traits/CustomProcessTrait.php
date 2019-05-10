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
        /**@var []CustomProcessInterface $processList */
        $processList = [];
        foreach ($processes as $item) {
            if (empty($item['class'])) {
                throw new \InvalidArgumentException(sprintf(
                        'process class name must be specified'
                    )
                );
            }
            $process = $item['class'];
            $processHandler = function (Process $worker) use ($swoole, $processPrefix, $process, $laravelConfig) {
                if (!isset(class_implements($process)[CustomProcessInterface::class])) {
                    $this->error(
                        sprintf(
                            '%s must implement the interface %s',
                            $process,
                            CustomProcessInterface::class
                        )
                    );
                }
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
                isset($item['redirect']) ? $item['redirect'] : false,
                isset($item['pipe']) ? $item['pipe'] : 0
            );
            if ($swoole->addProcess($customProcess)) {
                $processList[] = $customProcess;
            }
        }
        return $processList;
    }

}
