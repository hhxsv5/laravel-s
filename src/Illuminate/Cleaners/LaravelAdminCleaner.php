<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Encore\Admin\Admin;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class LaravelAdminCleaner implements CleanerInterface
{
    public function clean(Container $app, Container $snapshot)
    {
        $app->forgetInstance(Admin::class);
        Facade::clearResolvedInstance(Admin::class);
    }
}