<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SwooleProcessCollector;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class TimerProcessMetricsCronJob extends CronJob
{
    public function interval()
    {
        return config('prometheus.collect_metrics_interval', 10) * 1000;
    }

    public function isImmediate()
    {
        return true;
    }

    public function run()
    {
        /**@var SwooleProcessCollector $processCollector */
        $processCollector = app(SwooleProcessCollector::class);
        $processCollector->collect([
            'process_id'   => 'timer',
            'process_type' => 'timer',
        ]);
    }
}