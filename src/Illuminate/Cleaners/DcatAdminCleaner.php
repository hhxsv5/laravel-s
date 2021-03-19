<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Support\Facades\Facade;

class DcatAdminCleaner extends BaseCleaner
{
    protected $instances = [
        'admin.app',
        'admin.asset',
        'admin.color',
        'admin.sections',
        'admin.extend',
        'admin.extend.update',
        'admin.extend.version',
        'admin.navbar',
        'admin.menu',
        'admin.context',
        'admin.web-uploader',
    ];

    public function clean()
    {
        foreach ($this->instances as $instance) {
            $this->currentApp->forgetInstance($instance);
            Facade::clearResolvedInstance($instance);
        }
    }
}