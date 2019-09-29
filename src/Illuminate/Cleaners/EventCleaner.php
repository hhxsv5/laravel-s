<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;

class EventCleaner implements CleanerInterface
{
    protected $reflection;

    public function __construct()
    {
        $this->reflection = new \ReflectionClass(Dispatcher::class);
    }

    public function clean(Container $app, Container $snapshot)
    {
        $listeners = $this->reflection->getProperty('listeners');
        $listeners->setAccessible(true);
        $listeners->setValue($app['events'], []);
    }
}