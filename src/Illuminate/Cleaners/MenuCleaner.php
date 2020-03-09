<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Support\Facades\Facade;

class MenuCleaner extends BaseCleaner
{
    public function clean()
    {
        $this->currentApp->forgetInstance('Lavary\Menu\Menu');
        Facade::clearResolvedInstance('Lavary\Menu\Menu');
    }
}
