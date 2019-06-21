<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Swoole\Http\Server;
use Swoole\Process;

trait TimerTrait
{
    public function addTimerProcess(Server $swoole, array $config, array $laravelConfig)
    {
        if (empty($config['enable']) || empty($config['jobs'])) {
            return;
        }

        $startTimer = function (Process $process) use ($swoole, $config, $laravelConfig) {
            $pidfile = dirname($swoole->setting['pid_file']) . '/laravels-timer-process.pid';
            file_put_contents($pidfile, $process->pid);
            $this->setProcessTitle(sprintf('%s laravels: timer process', $config['process_prefix']));
            $this->initLaravel($laravelConfig, $swoole);
            $timerIds = [];
            foreach ($config['jobs'] as $jobClass) {
                if (is_array($jobClass) && isset($jobClass[0])) {
                    $job = new $jobClass[0](isset($jobClass[1]) ? $jobClass[1] : []);
                } else {
                    $job = new $jobClass();
                }
                if (!($job instanceof CronJob)) {
                    throw new \InvalidArgumentException(sprintf(
                            '%s must extend the abstract class %s',
                            get_class($job),
                            CronJob::class
                        )
                    );
                }
                if (empty($job->interval())) {
                    throw new \InvalidArgumentException(sprintf('The interval of %s cannot be empty', get_class($job)));
                }
                $runProcess = function () use ($job) {
                    $runCallback = function () use ($job) {
                        $this->callWithCatchException(function () use ($job) {
                            $job->run();
                        });
                    };
                    class_exists('Swoole\Coroutine') ? go($runCallback) : $runCallback();
                };

                $timerId = swoole_timer_tick($job->interval(), $runProcess);
                $timerIds[] = $timerId;
                $job->setTimerId($timerId);
                if ($job->isImmediate()) {
                    swoole_timer_after(1, $runProcess);
                }
            }

            Process::signal(SIGUSR1, function ($signo) use ($config, $timerIds, $process) {
                foreach ($timerIds as $timerId) {
                    swoole_timer_clear($timerId);
                }
                swoole_timer_after($config['max_wait_time'] * 1000, function () use ($process) {
                    $process->exit(0);
                });
            });
        };

        $timerProcess = new Process($startTimer, false, false);
        if ($swoole->addProcess($timerProcess)) {
            return $timerProcess;
        }
    }
}