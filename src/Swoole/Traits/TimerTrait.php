<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

trait TimerTrait
{
    use ProcessTitleTrait;
    use LaravelTrait;
    use LogTrait;

    public function addTimerProcess(\swoole_server $swoole, array $config, array $laravelConfig)
    {
        if (empty($config['enable']) || empty($config['jobs'])) {
            return;
        }

        $startTimer = function () use ($swoole, $config, $laravelConfig) {
            $this->setProcessTitle(sprintf('%s laravels: timer process', $config['process_prefix']));
            $this->initLaravel($laravelConfig, $swoole);
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
                if ($job->isImmediate()) {
                    swoole_timer_after(1, function () use ($job) {
                        try {
                            $job->run();
                        } catch (\Exception $e) {
                            $this->logException($e);
                        }
                    });
                }
            }
        };

        $timerProcess = new \swoole_process($startTimer, false, false);
        if ($swoole->addProcess($timerProcess)) {
            return $timerProcess;
        }
    }

}