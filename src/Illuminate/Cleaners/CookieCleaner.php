<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;

class CookieCleaner extends BaseCleaner
{
    private $queued;

    public function __construct(Container $currentApp, Container $snapshotApp)
    {
        parent::__construct($currentApp, $snapshotApp);
        if (!isset($this->currentApp['cookie'])) {
            return;
        }
        $ref = new \ReflectionObject($this->currentApp['cookie']);
        $this->queued = $ref->getProperty('queued');
        $this->queued->setAccessible(true);
    }

    public function clean()
    {
        if (!isset($this->currentApp['cookie'])) {
            return;
        }
        $this->queued->setValue($this->currentApp['cookie'], []);
    }
}