<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;


use Illuminate\Container\Container;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Facade;

class SessionCleaner extends BaseCleaner
{
    private $drivers;

    public function __construct(Container $currentApp, Container $snapshotApp)
    {
        parent::__construct($currentApp, $snapshotApp);

        if (!isset($this->currentApp['session'])) {
            return;
        }
        $ref = new \ReflectionObject($this->currentApp['session']);
        $this->drivers = $ref->getProperty('drivers');
        $this->drivers->setAccessible(true);

    }

    public function clean()
    {
        if (!isset($this->currentApp['session'])) {
            return;
        }

        $this->drivers->setValue($this->currentApp['session'], []);
        $this->currentApp->forgetInstance('session.store');
        Facade::clearResolvedInstance('session.store');

        if (isset($this->currentApp['redirect'])) {
            /**@var Redirector $redirect */
            $redirect = $this->currentApp['redirect'];
            $redirect->setSession($this->currentApp->make('session.store'));
        }
    }
}