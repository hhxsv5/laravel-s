<?php

namespace Hhxsv5\LaravelS\Swoole\Events;

interface WorkerStartInterface
{
    public function __construct();

    public function handle(\swoole_http_server $server, $workerId);
}