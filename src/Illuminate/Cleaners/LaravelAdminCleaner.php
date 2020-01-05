<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class LaravelAdminCleaner implements CleanerInterface
{
    public function clean(Container $app, Container $snapshot)
    {
        $app->forgetInstance('Encore\Admin\Admin');
        Facade::clearResolvedInstance('Encore\Admin\Admin');
    }
}