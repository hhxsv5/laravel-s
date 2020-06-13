<?php

namespace Hhxsv5\LaravelS\Swoole\Timer;

/**
 * This CronJob is used to ensure that timer process does not exit when all timers are cleared(stopped).
 * Class BackupCronJob
 * @package Hhxsv5\LaravelS\Swoole\Timer
 */
class BackupCronJob extends CronJob
{
    public function interval()
    {
        return 12 * 3600 * 1000;
    }

    public function isImmediate()
    {
        return false;
    }

    public function run()
    {
        // Do nothing.
    }
}