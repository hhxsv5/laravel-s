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
        return intval(self::GLOBAL_TIMER_LOCK_SECONDS * 0.9) * 1000;
    }

    public function isImmediate()
    {
        return false;
    }

    public function run()
    {
        if (!self::isGlobalTimerAlive() && self::getGlobalTimerLock()) {
            self::setEnable(true);
        }
    }
}