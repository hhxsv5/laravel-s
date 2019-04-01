<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;


use Illuminate\Container\Container;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Facade;

class SessionCleaner implements CleanerInterface
{
    public function clean(Container $app, Container $snapshot)
    {
        if (!$app->offsetExists('session')) {
            return;
        }

        $ref = new \ReflectionObject($app['session']);
        $drivers = $ref->getProperty('drivers');
        $drivers->setAccessible(true);
        $drivers->setValue($app['session'], []);

        $app->forgetInstance('session.store');
        Facade::clearResolvedInstance('session.store');

        if ($app->offsetExists('redirect')) {
            /**@var Redirector $redirect */
            $redirect = $app->offsetGet('redirect');
            $redirect->setSession($app->make('session.store'));
        }
    }
}