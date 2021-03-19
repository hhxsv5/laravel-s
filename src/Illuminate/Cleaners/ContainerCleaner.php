<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;


use Illuminate\Container\Container;

class ContainerCleaner extends BaseCleaner
{
    private $properties = [
        // Property => Initial value
        'reboundCallbacks' => [],
    ];

    private $cleanProperties = [
        // Property => ReflectionObject
    ];

    public function __construct(Container $currentApp, Container $snapshotApp)
    {
        parent::__construct($currentApp, $snapshotApp);
        $currentReflection = new \ReflectionObject($this->currentApp);
        $defaultValues = $currentReflection->getDefaultProperties();
        foreach ($this->properties as $property => &$initValue) {
            if ($currentReflection->hasProperty($property)) {
                $this->cleanProperties[$property] = $currentReflection->getProperty($property);
                $this->cleanProperties[$property]->setAccessible(true);
                $initValue = $defaultValues[$property];
            }
        }
        unset($initValue);
    }

    public function clean()
    {
        foreach ($this->cleanProperties as $property => $reflection) {
            $reflection->setValue($this->currentApp, $this->properties[$property]);
        }
    }
}