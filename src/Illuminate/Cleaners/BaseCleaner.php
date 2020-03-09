<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;

abstract class BaseCleaner implements CleanerInterface
{
    protected $currentApp;
    protected $snapshotApp;

    public function __construct(Container $currentApp, Container $snapshotApp)
    {
        $this->currentApp = $currentApp;
        $this->snapshotApp = $snapshotApp;
    }
}