<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Support\Facades\Facade;

class UrlCleaner extends BaseCleaner
{
    public function clean()
    {
        $this->currentApp->forgetInstance('url');
        Facade::clearResolvedInstance('url');
    }
}