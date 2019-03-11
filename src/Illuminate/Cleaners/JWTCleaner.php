<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class JWTCleaner implements CleanerInterface
{
    protected $instances = [
        'tymon.jwt',
        'tymon.jwt.auth',
        'tymon.jwt.parser',
        'tymon.jwt.claim.factory',
    ];

    public function clean(Container $app)
    {
        foreach ($this->instances as $instance) {
            $app->forgetInstance($instance);
            Facade::clearResolvedInstance($instance);
        }
    }
}