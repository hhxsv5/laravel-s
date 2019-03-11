<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class JWTCleaner implements CleanerInterface
{
    public function clean(Container $app)
    {
        $app->forgetInstance('tymon.jwt');
        Facade::clearResolvedInstance('tymon.jwt');
    }
}