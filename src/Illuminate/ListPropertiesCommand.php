<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Console\Command;

/**
 * This command is writing for those who want to know if there's any controller properties defined.
 * As controller is a singleton in laravel-s, all properties defined in controller will be retained after the request is finished.
 * So if you want to migrate to laravel-s, or just debug your app to find out potential problems, you can try this.
 */
class ListPropertiesCommand extends Command
{
    public $signature = 'laravels:list-properties';

    public $description = 'List all properties of all controllers.';

    /**
     * @throws \ReflectionException
     */
    public function fire()
    {
        return $this->handle();
    }

    /**
     * @throws \ReflectionException
     */
    public function handle()
    {
        $this->outputTable();
    }

    /**
     * Output all properties of all controllers as table.
     *
     * @throws \ReflectionException
     */
    private function outputTable()
    {
        $allProperties = $this->allControllerProperties();
        foreach ($allProperties as $controller => $properties) {
            if (empty($properties)) {
                continue;
            }

            $this->table(
                ['Controller', 'Property', 'Property Modifier'],
                $properties
            );
        }
    }

    /**
     * Get all properties of all controllers.
     *
     * @return array
     * @throws \ReflectionException
     */
    private function allControllerProperties()
    {
        $controllers = $this->allControllers();
        array_walk($controllers, function (&$properties, $controller) {
            $properties = [];
            // Get parent's properties
            $parent = get_parent_class($controller);
            if ($parent) {
                $reflectParentController = new \ReflectionClass($parent);
                $parentProperties = $reflectParentController->getProperties();
                foreach ($parentProperties as $property) {
                    $properties[$property->getName()] = [
                        $controller         => $controller,
                        'Property'          => $property->getName(),
                        'Property Modifier' => $this->resolveModifiers($property),
                    ];
                }
            }

            // Get sub controller's properties, override the parent properties.
            $reflectController = new \ReflectionClass($controller);
            $subProperties = $reflectController->getProperties();
            foreach ($subProperties as $property) {
                $properties[$property->getName()] = [
                    $controller         => $controller,
                    'Property'          => $property->getName(),
                    'Property Modifier' => $this->resolveModifiers($property),
                ];
            }
        });
        return $controllers;
    }

    /**
     * Get all controllers
     *
     * @return array
     * @throws \ReflectionException
     */
    private function allControllers()
    {
        $controllers = [];
        $router = isset(app()->router) ? app()->router : (app()->offsetExists('router') ? app('router') : app());
        $routes = $router->getRoutes();
        if (is_array($routes)) {
            $uses = array_column(array_column($routes, 'action'), 'uses');
        } else {
            $property = new \ReflectionProperty(get_class($routes), 'actionList');
            $property->setAccessible(true);
            $uses = array_keys($property->getValue($routes));
        }

        foreach ($uses as $use) {
            list($controller,) = explode('@', $use);
            $controllers[$controller] = $controller;
        }
        return $controllers;
    }

    /**
     * Resolve modifiers from \ReflectionProperty
     *
     * @param \ReflectionProperty $property
     * @return string
     */
    private function resolveModifiers(\ReflectionProperty $property)
    {
        if ($property->isPublic()) {
            $modifier = 'public';
        } elseif ($property->isProtected()) {
            $modifier = 'protected';
        } elseif ($property->isPrivate()) {
            $modifier = 'private';
        } else {
            $modifier = ' ';
        }
        if ($property->isStatic()) {
            $modifier .= ' static';
        }
        return $modifier;
    }
}