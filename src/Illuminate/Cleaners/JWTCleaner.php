<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Support\Facades\Facade;

class JWTCleaner extends BaseCleaner
{
    protected $instances = [
        'tymon.jwt',
        'tymon.jwt.auth',
        'tymon.jwt.parser',
        'tymon.jwt.claim.factory',
        'tymon.jwt.manager',
    ];

    public function clean()
    {
        foreach ($this->instances as $instance) {
            $this->currentApp->forgetInstance($instance);
            Facade::clearResolvedInstance($instance);
        }
    }
}