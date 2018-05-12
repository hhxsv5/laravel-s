<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

trait TimerTrait
{
    use ProcessTitleTrait;

    public function addTimerProcess(\swoole_server $swoole, array $config)
    {
        if (empty($config['enable']) || empty($config['jobs'])) {
            return;
        }

        $startTimer = function () use ($config) {
            $this->setProcessTitle(sprintf('%s laravels: timer process', $config['process_prefix']));
            $this->initLaravel();
            foreach ($config['jobs'] as $jobClass) {
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
        $swoole->addProcess($timerProcess);
    }

}