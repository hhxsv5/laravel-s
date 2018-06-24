<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;

trait CustomProcessTrait
{
    use ProcessTitleTrait;
    use LaravelTrait;

    public function addCustomProcesses(\swoole_server $swoole, $processPrefix, array $processes, array $laravelConfig)
    {
        $this->initLaravel($laravelConfig, $swoole);

        /**
         * @var []CustomProcessInterface $processList
         */
        $processList = [];
        foreach ($processes as $process) {
            $processHandler = function () use ($swoole, $processPrefix, $process, $laravelConfig) {
                $name = $process::getName() ?: 'custom';
                $this->setProcessTitle(sprintf('%s laravels: %s process', $processPrefix, $name));
                $this->initLaravel($laravelConfig, $swoole);
                $process::callback($swoole);
            };
            $customProcess = new \swoole_process($processHandler, $process::isRedirectStdinStdout(), $process::getPipeType());
            if ($swoole->addProcess($customProcess)) {
                $processList[] = $customProcess;
            }
        }
        return $processList;
    }

}