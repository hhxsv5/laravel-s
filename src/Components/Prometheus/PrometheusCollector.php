<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

abstract class PrometheusCollector implements PrometheusCollectorInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}