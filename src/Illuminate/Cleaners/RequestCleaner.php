<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class RequestCleaner implements CleanerInterface
{
    public function clean(Container $app)
    {
        $app->forgetInstance('request');
        Facade::clearResolvedInstance('request');
    }
}