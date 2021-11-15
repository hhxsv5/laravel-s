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

        // Key Format: prefix+metric_name+metric_type+metric_labels
        $memoryKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_worker_memory_usage', 'gauge', $labels]);
        $realMemoryKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_worker_memory_real_usage', 'gauge', $labels]);
        apcu_store($memoryKey, memory_get_usage(), $this->config['apcu_key_max_age']);
        apcu_store($realMemoryKey, memory_get_usage(true), $this->config['apcu_key_max_age']);

        if (PHP_VERSION_ID >= 70300) {
            $gcRunsKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_worker_gc_runs', 'gauge', $labels]);
            $gcCollectedKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_worker_gc_collected', 'gauge', $labels]);
            $gcThreshold = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_worker_gc_threshold', 'gauge', $labels]);
            $gcRootsKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_worker_gc_roots', 'gauge', $labels]);
            $gcStatus = gc_status();
            apcu_store($gcRunsKey, $gcStatus['runs'], $this->config['apcu_key_max_age']);
            apcu_store($gcCollectedKey, $gcStatus['collected'], $this->config['apcu_key_max_age']);
            apcu_store($gcThreshold, $gcStatus['threshold'], $this->config['apcu_key_max_age']);
            apcu_store($gcRootsKey, $gcStatus['roots'], $this->config['apcu_key_max_age']);
        }
    }
}