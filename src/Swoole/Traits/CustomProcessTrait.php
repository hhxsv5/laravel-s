<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

trait CustomProcessTrait
{
    use ProcessTitleTrait;
    use LaravelTrait;

    public function addCustomProcesses(\swoole_server $swoole, $processPrefix, array $processes, array $laravelConfig)
    {
        $processList = [];
        foreach ($processes as $process) {
            $processHandler = function () use ($swoole, $processPrefix, $process, $laravelConfig) {
                $name = isset($process['name']) ? $process['name'] : 'custom';
                $this->setProcessTitle(sprintf('%s laravels: %s process', $processPrefix, $name));
                $this->initLaravel($laravelConfig, $swoole);
                if (function_exists('\Swoole\Coroutine::call_user_func_array')) {
                    \Swoole\Coroutine::call_user_func_array($process['callback'], [$swoole, $process]);
                } else {
                    call_user_func_array($process['callback'], [$swoole, $process]);
                }
            };
            $customProcess = new \swoole_process($processHandler, $process['redirect_stdin_stdout'], $process['pipe_type']);
            if ($swoole->addProcess($customProcess)) {
                $processList[] = $customProcess;
            }
        }
        return $processList;
    }

}