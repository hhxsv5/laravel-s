<?php

namespace Hhxsv5\LaravelS\Components\Prometheus\Collectors;

use Hhxsv5\LaravelS\Components\MetricCollector;

class SwooleProcessCollector extends MetricCollector
{
    public function collect(array $params = [])
    {
        // Worker Memory Stats
        $labels = http_build_query([
            'process_id'   => $params['process_id'],
            'process_type' => $params['process_type'],
        ]);

        // Memory Usage
        $memoryMetrics = [
            [
                'name'  => 'swoole_process_memory_usage',
                'type'  => 'gauge',
                'value' => memory_get_usage(),
            ],
            [
                'name'  => 'swoole_process_memory_real_usage',
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
                    'name'  => 'swoole_process_gc_runs',
                    'type'  => 'gauge',
                    'value' => $gcStatus['runs'],
                ],
                [
                    'name'  => 'swoole_process_gc_collected',
                    'type'  => 'gauge',
                    'value' => $gcStatus['collected'],
                ],
                [
                    'name'  => 'swoole_process_gc_threshold',
                    'type'  => 'gauge',
                    'value' => $gcStatus['threshold'],
                ],
                [
                    'name'  => 'swoole_process_gc_roots',
                    'type'  => 'gauge',
                    'value' => $gcStatus['roots'],
                ],
            ];
        }
        $apcuKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_process_stats', '', $labels]);
        apcu_store($apcuKey, array_merge($memoryMetrics, $gcMetrics), $this->config['apcu_key_max_age']);
    }
}