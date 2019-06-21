<?php

namespace Hhxsv5\LaravelS\Console;

use Illuminate\Console\Command;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

/**
 * This command is writing for those who want to know if there's any controller properties defined.
 * As controller is a singleton in laravel-s, all properties defined in controller will be retained after the request is finished.
 * So if you want to migrate to laravel-s, or just debug your app to find out potential problems, you can try this.
 */
class ListPropertiesCommand extends Command
{
    public $signature = 'laravels:list-properties';

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

        $properties->each(function ($properties, $action) {
            $controllerName = explode('@', $action)[0];

            $this->comment($controllerName);

            $this->table(
                $this->headers(),
                $properties
            );
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

        $properties = $controllers
            ->map(function (Controller $controller) {
                // Get related controller's properties
                $reflectController = new \ReflectionClass($controller);
                $properties = $reflectController->getProperties();

                // Get parent's properties
                $parent = get_parent_class(get_class($controller));
                $reflectParentController = new \ReflectionClass($parent);
                $parentProperties = collect($reflectParentController->getProperties())
                    ->map
                    ->getName()
                    ->flip()
                    ->toArray();

                return collect($properties)
                    ->map(function (\ReflectionProperty $reflectionProperty) use ($controller, $parentProperties) {
                        // Exclude parent's properties
                        if (!array_key_exists($reflectionProperty->getName(), $parentProperties)) {
                            return [
                                'name' => $reflectionProperty->getName(),
                                'property' => $this->resolveModifiers($reflectionProperty)  . '$' . $reflectionProperty->getName(),
                            ];
                        }
                    })
                    ->filter()
                    ->toArray();
            });

        return $properties;
    }

    /**
     * Get all controllers
     *
     * @return Collection
     * @throws \ReflectionException
     */
    private function allControllers()
    {
        $actionList = $this->actionList();

        return collect($actionList)->map->getController();
    }

    /**
     * Get actionList from RouteCollection
     *
     * @return array
     * @throws \ReflectionException
     */
    private function actionList()
    {
        $routeCollection = app('router')->getRoutes();

        $actionListReflectProperty = new \ReflectionProperty(get_class($routeCollection), 'actionList');
        $actionListReflectProperty->setAccessible(true);

        return $actionListReflectProperty->getValue($routeCollection);
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
}