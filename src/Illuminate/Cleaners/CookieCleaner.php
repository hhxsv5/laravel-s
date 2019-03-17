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
        /**@var \Illuminate\Cookie\CookieJar $appCookie */
        $appCookie = $app->offsetGet('cookie');
        /**@var \Symfony\Component\HttpFoundation\Cookie[] $cookies */
        $cookies = $appCookie->getQueuedCookies();
        foreach ($cookies as $name => $cookie) {
            $appCookie->unqueue($name);
        }
    }
}