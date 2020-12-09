<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Illuminate\Contracts\Console\Kernel;

class LaravelScheduleJob extends CronJob
{
    protected $artisan;

    public function interval()
    {
        return 60 * 1000;// Run every 1 minute
    }

    public function isImmediate()
    {
        return false;
    }

    public function run()
    {
        app(Kernel::class)->call('schedule:run');
    }
}