<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;


use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class AuthCleaner extends BaseCleaner
{
    private $guards;

    public function __construct(Container $currentApp, Container $snapshotApp)
    {
        parent::__construct($currentApp, $snapshotApp);

        if (!isset($this->currentApp['auth'])) {
            return;
        }
        $ref = new \ReflectionObject($this->currentApp['auth']);
        if ($ref->hasProperty('guards')) {
            $this->guards = $ref->getProperty('guards');
        } else {
            $this->guards = $ref->getProperty('drivers');
        }
        $this->guards->setAccessible(true);
    }

    public function clean()
    {
        if (!isset($this->currentApp['auth'])) {
            return;
        }
        $this->guards->setValue($this->currentApp['auth'], []);
        $this->currentApp->forgetInstance('auth.driver');
        Facade::clearResolvedInstance('auth.driver');
    }
}