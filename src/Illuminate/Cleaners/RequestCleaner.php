<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Support\Facades\Facade;

class RequestCleaner extends BaseCleaner
{
    public function clean()
    {
        $this->currentApp->forgetInstance('request');
        Facade::clearResolvedInstance('request');
    }
}