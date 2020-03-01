<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;

class CookieCleaner implements CleanerInterface
{
    public function clean(Container $app, Container $snapshot)
    {
        if (!$app->offsetExists('cookie')) {
            return;
        }
        $cookie = $app->offsetGet('cookie');
        $ref = new \ReflectionObject($cookie);
        $queued = $ref->getProperty('queued');
        $queued->setAccessible(true);
        $queued->setValue($cookie, []);
    }
}