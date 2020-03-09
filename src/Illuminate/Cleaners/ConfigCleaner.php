<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

class ConfigCleaner extends BaseCleaner
{
    public function clean()
    {
        $this->currentApp['config']->set($this->snapshotApp['config']->all());
    }
}