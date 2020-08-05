<?php

namespace Hhxsv5\LaravelS\Swoole\Timer;

/**
 * This CronJob is used to renew the cache key of global timer.
 * Class RenewGlobalTimerLockCronJob
 * @package Hhxsv5\LaravelS\Swoole\Timer
 */
class RenewGlobalTimerLockCronJob extends CronJob
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
        $this->renewGlobalTimerLock(self::GLOBAL_TIMER_LOCK_SECONDS);
    }
}