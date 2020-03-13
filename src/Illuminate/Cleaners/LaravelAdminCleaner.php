<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class LaravelAdminCleaner extends BaseCleaner
{
    const   ADMIN_CLASS = 'Encore\Admin\Admin';

    private $reflection;

    protected $properties = [
        'deferredScript' => [],
        'script'         => [],
        'style'          => [],
        'css'            => [],
        'js'             => [],
        'html'           => [],
        'headerJs'       => [],
        'manifestData'   => [],
        'extensions'     => [],
        'minifyIgnores'  => [],
    ];

    public function __construct(Container $currentApp, Container $snapshotApp)
    {
        parent::__construct($currentApp, $snapshotApp);
        $this->reflection = new \ReflectionClass(self::ADMIN_CLASS);
    }

    public function clean()
    {
        foreach ($this->properties as $name => $value) {
            if (property_exists(self::ADMIN_CLASS, $name)) {
                $this->reflection->setStaticPropertyValue($name, $value);
            }
        }
        $this->currentApp->forgetInstance(self::ADMIN_CLASS);
        Facade::clearResolvedInstance(self::ADMIN_CLASS);
    }
}