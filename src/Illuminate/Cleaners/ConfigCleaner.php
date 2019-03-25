<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;

class ConfigCleaner implements CleanerInterface
{
    public function clean(Container $app, Container $snapshot)
    {
        $app['config']->set($snapshot['config']->all());
    }
}