<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class JWTCleaner implements CleanerInterface
{
    public function clean(Container $app)
    {
        $app->forgetInstance('tymon.jwt');
        $app->forgetInstance('tymon.jwt.auth');
        $app->forgetInstance('tymon.jwt.parser');
        $app->forgetInstance('tymon.jwt.claim.factory');
        Facade::clearResolvedInstance('tymon.jwt');
        Facade::clearResolvedInstance('tymon.jwt.auth');
        Facade::clearResolvedInstance('tymon.jwt.parser');
        Facade::clearResolvedInstance('tymon.jwt.claim.factory');
    }
}