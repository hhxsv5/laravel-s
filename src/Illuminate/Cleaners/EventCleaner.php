<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;


use Illuminate\Container\Container;

class EventCleaner extends BaseCleaner
{
    private $listeners;
    private $wildcards;
    private $wildcardsCache;

    public function __construct(Container $currentApp, Container $snapshotApp)
    {
        parent::__construct($currentApp, $snapshotApp);

        $ref = new \ReflectionObject($this->currentApp['events']);
        $this->listeners = $ref->getProperty('listeners');
        $this->listeners->setAccessible(true);

        $this->wildcards = $ref->getProperty('wildcards');
        $this->wildcards->setAccessible(true);

        if ($ref->hasProperty('wildcardsCache')) { // Laravel 5.6+
            $this->wildcardsCache = $ref->getProperty('wildcardsCache');
            $this->wildcardsCache->setAccessible(true);
        }
    }

    public function clean()
    {
        $this->listeners->setValue($this->currentApp['events'], []);
        $this->wildcards->setValue($this->currentApp['events'], []);
        if ($this->wildcardsCache) {
            $this->wildcardsCache->setValue($this->currentApp['events'], []);
        }
    }
}