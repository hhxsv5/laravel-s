<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

class EventCleaner implements CleanerInterface
{
    public function clean(Container $app, Container $snapshot)
    {
        $app->forgetInstance('events');
        Facade::clearResolvedInstance('events');
        Model::setEventDispatcher($app['events']);
    }
}