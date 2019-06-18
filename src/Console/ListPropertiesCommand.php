<?php

namespace Hhxsv5\LaravelS\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * This command is writing for those who want to know if there's any controller properties defined.
 * As controller is a singleton in laravel-s, all properties defined in controller will be retained after the request is finished.
 * So if you want to migrate to laravel-s, or just debug your app to find out potential problems, you can try this.
 */
class ListPropertiesCommand extends Command
{
    public $signature = 'laravels:list-properties {--api-version=}';

    public $description = 'List all properties of all controllers (not include parent\'s).';

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
        $properties = $this->allControllerProperties();

        $properties->each(function (Collection $properties) {
            $properties->groupBy('controller')
                ->map(function (Collection $properties, $controller) {
                    // Controller name
                    $this->comment($controller);

                    $properties = $properties->map(function ($property) {
                        unset($property['controller']);
                        return $property;
                    })->toArray();

                    $this->table(
                        $this->headers(),
                        $properties
                    );
                });
        });
    }

    /**
     * Table headers.
     *
     * @return array
     */
    private function headers()
    {
        return ['property', 'name'];
    }

    /**
     * Get all properties of all controllers.
     *
     * @return Collection
     * @throws \ReflectionException
     */
    private function allControllerProperties()
    {
        $controllers = $this->allControllers();

        /** @var \Illuminate\Routing\Controller|\Laravel\Lumen\Routing\Controller $controller */
        return $controllers
            ->map(function ($controller) {
                $parentProperties = [];

                // Get related controller's properties
                $reflectController = new \ReflectionClass($controller);
                $properties = $reflectController->getProperties();

                // Get parent's properties
                $parent = get_parent_class(get_class($controller));
                if ($parent) {
                    $reflectParentController = new \ReflectionClass($parent);
                    $parentProperties = collect($reflectParentController->getProperties())
                        ->map
                        ->getName()
                        ->flip()
                        ->toArray();
                }

                return collect($properties)
                    ->map(function (\ReflectionProperty $reflectionProperty) use ($controller, $parentProperties) {
                        // Exclude parent's properties
                        if (!array_key_exists($reflectionProperty->getName(), $parentProperties)) {
                            return [
                                'controller' => get_class($controller),
                                'name' => $reflectionProperty->getName(),
                                'property' => $this->resolveModifiers($reflectionProperty) . '$' . $reflectionProperty->getName(),
                            ];
                        }
                    })
                    ->filter();
            });
    }

    /**
     * Get all controllers
     *
     * @return Collection
     * @throws \ReflectionException
     */
    private function allControllers()
    {
        if ($this->isLumen()) {
            $controllers = $this->lumenControllers();
        } else {
            $controllers = $this->laravelControllers();
        }

        if ($this->isIntegrateWithDingo()) {
            $controllers = $controllers->merge($this->dingoControllers());
        }

        return $controllers;
    }

    /**
     * Get actionList from RouteCollection
     *
     * @return array
     * @throws \ReflectionException
     */
    private function laravelControllers()
    {
        $routeCollection = app('router')->getRoutes();

        $actionListReflectProperty = new \ReflectionProperty(get_class($routeCollection), 'actionList');
        $actionListReflectProperty->setAccessible(true);

        $routes = $actionListReflectProperty->getValue($routeCollection);

        return collect($routes)->map->getController();
    }

    /**
     * Get controllers used in routes for Lumen.
     *
     * @return Collection
     */
    private function lumenControllers()
    {
        $routes = app('router')->getRoutes();

        return collect($routes)
            ->map(function ($route) {
                $controllerClass = $this->resolveController($route);
                if ($controllerClass) {
                    return app($controllerClass);
                }
            })
            ->unique()
            ->filter()
            ->values();
    }

    /**
     * Get all controllers used in routes for dingo/api.
     *
     * @return Collection
     */
    private function dingoControllers()
    {
        $routes = app('api.router')->getRoutes();
        $version = $this->resolveApiVersion();

        if (!isset($routes[$version])) {
            throw new \RuntimeException("Dingo Api Version {$version} not defined.");
        }

        // Get all routes of specify version.
        $routes = $routes[$version];

        return collect($routes)
            ->map
            ->getController()
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Get related controller of Lumen route.
     *
     * @param array $lumenRoute
     * @return string|null
     */
    private function resolveController($lumenRoute)
    {
        // Something like "App\Http\Controllers\SomeController@index"
        $uses = array_get($lumenRoute, 'action.uses');

        if (strpos($uses, '@') !== false) {
            return explode('@', $uses)[0];
        }
    }

    /**
     * Resolve modifiers from \ReflectionProperty
     *
     * @param \ReflectionProperty $reflectionProperty
     * @return string
     */
    private function resolveModifiers(\ReflectionProperty $reflectionProperty)
    {
        if ($reflectionProperty->isPublic()) {
            $prefix = 'public';
        } elseif ($reflectionProperty->isProtected()) {
            $prefix = 'protected';
        } elseif ($reflectionProperty->isPrivate()) {
            $prefix = 'private';
        } else {
            $prefix = ' ';
        }

        return $reflectionProperty->isStatic() ? "{$prefix} static " : $prefix . ' ';
    }

    /**
     * Determine if is integrate with dingo/api.
     *
     * @return bool
     */
    private function isIntegrateWithDingo()
    {
        return app()->bound('api.router');
    }

    /**
     * Resolve dingo api version from commandline options or get default version defined in .env
     *
     * @return string
     */
    private function resolveApiVersion()
    {
        return $this->option('api-version') ?: $this->currentApiVersion();
    }

    /**
     * Get default api version.
     *
     * @return string
     */
    private function currentApiVersion()
    {
        return config('api.version', 'v1');
    }

    /**
     * Determine if is lumen framework.
     *
     * @return bool
     */
    private function isLumen()
    {
        return str_contains(strtolower(app()->version()), 'lumen');
    }
}
