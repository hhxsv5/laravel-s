<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

class Exporter
{
    const REDNER_MIME_TYPE = 'text/plain; version=0.0.4';

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getMetrics()
    {
        $apcSmaInfo = apcu_sma_info(true);
        $metrics = [
            [
                'name'  => 'apcu_seg_size',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $apcSmaInfo['seg_size'],
            ],
            [
                'name'  => 'apcu_avail_mem',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $apcSmaInfo['avail_mem'],
            ],
        ];
        foreach (new \APCuIterator('/^' . $this->config['apcu_key_prefix'] . $this->config['apcu_key_separator'] . '/') as $item) {
            $parts = explode($this->config['apcu_key_separator'], $item['key']);
            parse_str($parts[3], $labels);
            if (is_array($item['value'])) {
                foreach ($item['value'] as $metric) {
                    $metrics[] = [
                        'name'   => $metric['name'],
                        'help'   => '',
                        'type'   => $metric['type'],
                        'value'  => $metric['value'],
                        'labels' => $labels,
                    ];
                }
            } else {
                $metrics[] = [
                    'name'   => $parts[1],
                    'help'   => '',
                    'type'   => $parts[2],
                    'value'  => $item['value'],
                    'labels' => $labels,
                ];
            }
        }
        return $metrics;
    }

    public function render()
    {
        $defaultLabels = ['application' => $this->config['application']];
        $metrics = $this->getMetrics();
        $lines = [];
        foreach ($metrics as $metric) {
            $lines[] = "# HELP " . $metric['name'] . " {$metric['help']}";
            $lines[] = "# TYPE " . $metric['name'] . " {$metric['type']}";

            $metricLabels = isset($metric['labels']) ? $metric['labels'] : [];
            $labels = ['{'];
            $allLabels = array_merge($defaultLabels, $metricLabels);
            foreach ($allLabels as $key => $value) {
                $labels[] = "{$key}=\"{$value}\",";
            }
            $labels[] = '}';
            $labelStr = implode('', $labels);
            $lines[] = $metric['name'] . "$labelStr {$metric['value']}";
        }
        return implode("\n", $lines);
    }
}