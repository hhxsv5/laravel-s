<?php

namespace Hhxsv5\LaravelS\Swoole\Task;


use Hhxsv5\LaravelS\Swoole\Timer\CronJobInterface;

class TimerTask extends Task
{
    private $job;

    public function __construct(CronJobInterface $job)
    {
        $this->job = $job;
    }

    public function handle()
    {
        $this->job->run();
    }

    public function finish()
    {

    }
}