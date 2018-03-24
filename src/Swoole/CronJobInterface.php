<?php

namespace Hhxsv5\LaravelS\Swoole;

interface CronJobInterface
{
    /**
     * @return int $interval ms
     */
    public function frequency();

    public function run();
}