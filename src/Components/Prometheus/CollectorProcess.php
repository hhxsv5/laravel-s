<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SwooleProcessCollector;
use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SwooleStatsCollector;
use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SystemCollector;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Timer;

class CollectorProcess implements CustomProcessInterface
{
    private static $timerId;

    public static function getDefinition()
    {
        return [
            'prometheus' => [
                'class'    => static::class,
                'redirect' => false,
                'pipe'     => 0,
                'enable'   => (bool)config('prometheus.enable', true),
            ],
        ];
    }

    public static function callback(Server $swoole, Process $process)
    {
        /**@var SwooleProcessCollector $processCollector */
        $processCollector = app(SwooleProcessCollector::class);
        /**@var SwooleStatsCollector $swooleStatsCollector */
        $swooleStatsCollector = app(SwooleStatsCollector::class);
        /**@var SystemCollector $systemCollector */
        $systemCollector = app(SystemCollector::class);
        $workerNum = $swoole->setting['worker_num'];
        $taskWorkerNum = isset($swoole->setting['task_worker_num']) ? $swoole->setting['task_worker_num'] : 0;
        $totalNum = $workerNum + $taskWorkerNum - 1;
        $workerIds = range(0, $totalNum);
        $runJob = function () use ($swoole, $workerIds, $processCollector, $swooleStatsCollector, $systemCollector) {
            // Collect the metrics of Swoole stats()
            $swooleStatsCollector->collect();

            // Collect the metrics of system
            $systemCollector->collect();

            // Collect the metrics of all workers
            foreach ($workerIds as $workerId) {
                $swoole->sendMessage($processCollector, $workerId);
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