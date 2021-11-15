<?php

namespace Hhxsv5\LaravelS\Components\Prometheus\Collectors;

use Hhxsv5\LaravelS\Components\Prometheus\PrometheusCollector;

class SystemCollector extends PrometheusCollector
{
    public function collect(array $params = [])
    {
        $load = sys_getloadavg();
        $metrics = [
            [
                'name'  => 'system_load_average_1m',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $load[0],
            ],
            [
                'name'  => 'system_load_average_5m',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $load[1],
            ],
            [
                'name'  => 'system_load_average_15m',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $load[2],
            ],
        ];
        foreach ($metrics as $metric) {
            $key = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], $metric['name'], $metric['type'], '']);
            apcu_store($key, $metric['value'], $this->config['apcu_key_max_age']);
        }
    }
}