<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;


use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class AuthCleaner implements CleanerInterface
{
    public function clean(Container $app)
    {
        if (!$app->offsetExists('auth')) {
            return;
        }
        $ref = new \ReflectionObject($app['auth']);
        $drivers = $ref->getProperty('drivers');
        $drivers->setAccessible(true);
        $drivers->setValue($app['auth'], []);

        $app->forgetInstance('auth.driver');
        Facade::clearResolvedInstance('auth.driver');
    }
}