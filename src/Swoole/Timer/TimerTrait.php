<?php

namespace Hhxsv5\LaravelS\Swoole\Timer;

use Swoole\Event;
use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Timer;

trait TimerTrait
{
    private $timerPidFile = 'laravels-timer-process.pid';

    public function addTimerProcess(Server $swoole, array $config, array $laravelConfig)
    {
        if (empty($config['enable']) || empty($config['jobs'])) {
            return false;
        }

        // Add backup cron job.
        $config['jobs'][] = BackupCronJob::class;
        if (!empty($config['global_lock'])) {
            // Add auxiliary jobs for global timer.
            $config['jobs'][] = RenewGlobalTimerLockCronJob::class;
            $config['jobs'][] = CheckGlobalTimerAliveCronJob::class;
        }

        $callback = function (Process $process) use ($swoole, $config, $laravelConfig) {
            $pidfile = dirname($swoole->setting['pid_file']) . '/' . $this->timerPidFile;
            file_put_contents($pidfile, $process->pid);
            $this->setProcessTitle(sprintf('%s laravels: timer process', $config['process_prefix']));
            $this->initLaravel($laravelConfig, $swoole);

            // Implement global timer by Cache lock.
            if (!empty($config['global_lock'])) {
                CronJob::setGlobalTimerLockKey($config['global_lock_key']);
                CronJob::checkSetEnable();
            }

            $timerIds = $this->registerTimers($config['jobs']);

            Process::signal(SIGUSR1, function ($signo) use ($config, $timerIds, $process) {
                foreach ($timerIds as $timerId) {
                    if (Timer::exists($timerId)) {
                        Timer::clear($timerId);
                    }
                }
                Timer::after($config['max_wait_time'] * 1000, function () use ($process) {
                    $process->exit(0);
                });
            });
            // For Swoole 4.6.x
            // Deprecated: Swoole\Event::rshutdown(): Event::wait() in shutdown function is deprecated in Unknown on line 0
            Event::wait();
        };

        $process = new Process($callback, false, 0);
        $swoole->addProcess($process);
        return $process;
    }

    public function registerTimers(array $jobs)
    {
        $timerIds = [];
        foreach ($jobs as $jobClass) {
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
            $runJob = function () use ($job) {
                $runCallback = function () use ($job) {
                    $this->callWithCatchException(function () use ($job) {
                        if (($job instanceof CheckGlobalTimerAliveCronJob) || $job::isEnable()) {
                            $job->run();
                        }
                    });
                };
                class_exists('Swoole\Coroutine') ? \Swoole\Coroutine::create($runCallback) : $runCallback();
            };

            $timerId = Timer::tick($job->interval(), $runJob);
            $timerIds[] = $timerId;
            $job->setTimerId($timerId);
            if ($job->isImmediate()) {
                Timer::after(1, $runJob);
            }
        }
        return $timerIds;
    }
}