<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class LaravelAdminCleaner extends BaseCleaner
{
    const   ADMIN_CLASS = 'Encore\Admin\Admin';

    private $reflection;

    protected $properties = [
        'deferredScript'   => [],
        'script'           => [],
        'style'            => [],
        'css'              => [],
        'js'               => [],
        'html'             => [],
        'headerJs'         => [],
        'manifest'         => 'vendor/laravel-admin/minify-manifest.json',
        'manifestData'     => [],
        'extensions'       => [],
        'minifyIgnores'    => [],
        'metaTitle'        => null,
        'favicon'          => null,
        'bootingCallbacks' => [],
        'bootedCallbacks'  => [],
    ];

    public function __construct(Container $currentApp, Container $snapshotApp)
    {
        parent::__construct($currentApp, $snapshotApp);
        $this->reflection = new \ReflectionClass(self::ADMIN_CLASS);
    }

    public function clean()
    {
        foreach ($this->properties as $name => $value) {
            if ($this->reflection->hasProperty($name)) {
                $property = $this->reflection->getProperty($name);
                if ($property->isStatic()) {
                    if (!$property->isPublic()) {
                        $property->setAccessible(true);
                    }
                    $property->setValue($value);
                }
            }
        }
        $this->currentApp->forgetInstance(self::ADMIN_CLASS);
        Facade::clearResolvedInstance(self::ADMIN_CLASS);
    }
}