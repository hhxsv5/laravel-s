<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Console\Portal;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class LaravelScheduleJob extends CronJob
{
    protected $artisan;

    public function interval()
    {
        return 60 * 1000; // Run every 1 minute
    }

    public function isImmediate()
    {
        return false;
    }

    public function run()
    {
        Portal::runArtisanCommand(base_path(), 'schedule:run >> /dev/null 2>&1 &');
    }
}