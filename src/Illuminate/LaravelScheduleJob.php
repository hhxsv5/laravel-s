<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class LaravelScheduleJob extends CronJob
{
    protected $artisan;

    public function __construct()
    {
        $this->artisan = app('Illuminate\Contracts\Console\Kernel');
    }

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
        $this->artisan->call('schedule:run');
    }
}