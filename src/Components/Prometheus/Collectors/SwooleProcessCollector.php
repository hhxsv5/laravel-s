<?php

namespace Hhxsv5\LaravelS\Components\Prometheus\Collectors;

use Hhxsv5\LaravelS\Components\Prometheus\PrometheusCollector;

class SwooleProcessCollector extends PrometheusCollector
{
    public function collect(array $params = [])
    {
        /**@var \Swoole\Http\Server $swoole */
        $swoole = app('swoole');
        // Worker Memory Stats
        $labels = http_build_query([
            'worker_id'   => $swoole->worker_id,
            'worker_type' => $swoole->taskworker ? 'task' : 'worker',
        ]);

        // Memory Usage
        $memoryMetrics = [
            [
                'name'  => 'swoole_worker_memory_usage',
                'type'  => 'gauge',
                'value' => memory_get_usage(),
            ],
            [
                'name'  => 'swoole_worker_memory_real_usage',
                'type'  => 'gauge',
                'value' => memory_get_usage(true),
            ],
        ];

        // GC Status
        $gcMetrics = [];
        if (PHP_VERSION_ID >= 70300) {
            $gcStatus = gc_status();
            $gcMetrics = [
                [
                    'name'  => 'swoole_worker_gc_runs',
                    'type'  => 'gauge',
                    'value' => $gcStatus['runs'],
                ],
                [
                    'name'  => 'swoole_worker_gc_collected',
                    'type'  => 'gauge',
                    'value' => $gcStatus['collected'],
                ],
                [
                    'name'  => 'swoole_worker_gc_threshold',
                    'type'  => 'gauge',
                    'value' => $gcStatus['threshold'],
                ],
                [
                    'name'  => 'swoole_worker_gc_roots',
                    'type'  => 'gauge',
                    'value' => $gcStatus['roots'],
                ],
            ];
        }
        $apcuKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_process_stats', '', $labels]);
        apcu_store($apcuKey, array_merge($memoryMetrics, $gcMetrics), $this->config['apcu_key_max_age']);
    }
}