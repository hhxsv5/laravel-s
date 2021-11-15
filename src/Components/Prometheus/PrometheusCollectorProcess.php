<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SwooleProcessCollector;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Timer;

class PrometheusCollectorProcess implements CustomProcessInterface
{
    private static $timerId;

    public static function callback(Server $swoole, Process $process)
    {
        /**@var \Hhxsv5\LaravelS\Components\Prometheus\PrometheusCollector $collector */
        $collector = app(SwooleProcessCollector::class);
        $workerNum = $swoole->setting['worker_num'];
        $taskWorkerNum = isset($swoole->setting['task_worker_num']) ? $swoole->setting['task_worker_num'] : 0;
        $totalNum = $workerNum + $taskWorkerNum - 1;
        $workerIds = range(0, $totalNum);
        $runJob = function () use ($swoole, $workerIds, $collector) {
            foreach ($workerIds as $workerId) {
                $swoole->sendMessage($collector, $workerId);
            }
        };

        $interval = config('prometheus.collect_metrics_interval', 10) * 1000;
        self::$timerId = Timer::tick($interval, $runJob);
    }

    public static function onReload(Server $swoole, Process $process)
    {
        Timer::clear(self::$timerId);
    }

    public static function onStop(Server $swoole, Process $process)
    {
        Timer::clear(self::$timerId);
    }
}