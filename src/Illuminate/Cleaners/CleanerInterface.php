<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;

interface CleanerInterface
{
    public function clean(Container $app, Container $snapshot);
}