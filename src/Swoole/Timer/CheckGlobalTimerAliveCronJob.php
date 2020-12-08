<?php

namespace Hhxsv5\LaravelS\Swoole\Timer;

/**
 * This CronJob is used to check global timer alive.
 * Class CheckGlobalTimerAliveCronJob
 * @package Hhxsv5\LaravelS\Swoole\Timer
 */
class CheckGlobalTimerAliveCronJob extends CronJob
{
    public function interval()
    {
        return (int)(static::GLOBAL_TIMER_LOCK_SECONDS * 0.9) * 1000;
    }

    public function isImmediate()
    {
        return false;
    }

    public function run()
    {
        if (static::isGlobalTimerAlive()) {
            // Reset current timer to avoid repeated execution
            static::setEnable(static::isCurrentTimerAlive());
        } else {
            // Compete for timer lock
            static::setEnable(static::getGlobalTimerLock());
        }
    }
}