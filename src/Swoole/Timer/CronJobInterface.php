<?php

namespace Hhxsv5\LaravelS\Swoole\Timer;

interface CronJobInterface
{
    public function __construct();

    /**
     * @return int $interval ms
     */
    public function interval();

    public function run();

    public function stop();
}