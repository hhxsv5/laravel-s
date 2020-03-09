<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Support\Facades\Facade;

class LaravelAdminCleaner extends BaseCleaner
{
    public function clean()
    {
        $this->currentApp->forgetInstance('Encore\Admin\Admin');
        Facade::clearResolvedInstance('Encore\Admin\Admin');
    }
}