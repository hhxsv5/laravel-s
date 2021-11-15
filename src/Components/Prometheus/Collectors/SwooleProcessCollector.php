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
        $metrics = [
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
        $memoryKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], '', '', $labels]);
        apcu_store($memoryKey, $metrics, $this->config['apcu_key_max_age']);

        // GC Status
        if (PHP_VERSION_ID >= 70300) {
            $gcStatus = gc_status();
            $metrics = [
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
            $gcKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], '', '', $labels]);
            apcu_store($gcKey, $metrics, $this->config['apcu_key_max_age']);
        }
    }
}