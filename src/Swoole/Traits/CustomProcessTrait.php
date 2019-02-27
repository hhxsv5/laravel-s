<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;

trait CustomProcessTrait
{
    use ProcessTitleTrait;
    use LogTrait;

    public function addCustomProcesses(\swoole_server $swoole, $processPrefix, array $processes, array $laravelConfig)
    {
        if (!empty($processes)) {
            Laravel::autoload($laravelConfig['root_path']);
        }

        /**
         * @var []CustomProcessInterface $processList
         */
        $processList = [];
        foreach ($processes as $process) {
            if (!isset(class_implements($process)[CustomProcessInterface::class])) {
                throw new \Exception(sprintf(
                        '%s must implement the interface %s',
                        $process,
                        CustomProcessInterface::class
                    )
                );
            }
            $processHandler = function (\swoole_process $worker) use ($swoole, $processPrefix, $process, $laravelConfig) {
                $name = $process::getName() ?: 'custom';
                $this->setProcessTitle(sprintf('%s laravels: %s process', $processPrefix, $name));
                $this->initLaravel($laravelConfig, $swoole);

                swoole_event_add($worker->pipe, function ($pipe) use ($name, $process, $worker) {
                    $recv = $worker->read();
                    //echo "From Master: $recv\n";

                    if ($recv === 'signal:SIGUSR1') {
                        $this->info(sprintf('Reloading the process %s [pid=%d].', $name, $worker->pid));
                        $process::onReload($worker);

                        // send data to master
                        $worker->write('reload:done');

                        swoole_event_del($pipe);
                    }
                });

                $maxTry = 10;
                $i = 0;
                do {
                    $this->callWithCatchException([$process, 'callback'], [$swoole, $worker]);
                    ++$i;
                    sleep(1);
                } while ($i < $maxTry);
                $this->error(
                    sprintf(
                        'The custom process "%s" reaches the maximum number of retries %d times, and will be restarted by the manager process.',
                        $name,
                        $maxTry
                    )
                );
            };
            $customProcess = new \swoole_process(
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
