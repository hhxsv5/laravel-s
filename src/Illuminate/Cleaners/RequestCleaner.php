<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Support\Facades\Facade;

class RequestCleaner extends BaseCleaner
{
    public function clean()
    {
        unset($this->currentApp['url'], $this->currentApp['request'], $this->currentApp['swoole-http-response']);
        Facade::clearResolvedInstance('url');
        Facade::clearResolvedInstance('request');
        Facade::clearResolvedInstance('swoole-http-response');
    }
}