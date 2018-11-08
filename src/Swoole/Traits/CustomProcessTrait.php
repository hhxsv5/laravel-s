<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Illuminate\Laravel;

trait CustomProcessTrait
{
    use ProcessTitleTrait;

    public function addCustomProcesses(\swoole_server $swoole, $processPrefix, array $processes, array $laravelConfig)
    {
        Laravel::autoload($laravelConfig['root_path']);

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