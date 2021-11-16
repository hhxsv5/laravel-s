<?php

namespace Hhxsv5\LaravelS\Swoole\Timer;

use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SwooleProcessCollector;

class PrometheusTimerProcessMetricsCronJob extends CronJob
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
            'worker_id'   => 'timer',
            'worker_type' => 'timer',
        ]);
    }
}