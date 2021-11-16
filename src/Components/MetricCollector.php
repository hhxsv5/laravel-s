<?php

namespace Hhxsv5\LaravelS\Components;

abstract class MetricCollector implements MetricCollectorInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}