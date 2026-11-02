<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Illuminate;

use Hhxsv5\LaravelS\Illuminate\CleanerManager;
use Hhxsv5\LaravelS\Illuminate\Cleaners\BaseCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\ConfigCleaner;
use Hhxsv5\LaravelS\Tests\TestCase;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

class CleanerManagerTest extends TestCase
{
    public function testCustomCleanersRunOnceBeforeDefaultsAndAreSingletons(): void
    {
        $current = new Container();
        $snapshot = new Container();
        $current->instance('config', new Repository(['value' => 'changed']));
        $snapshot->instance('config', new Repository(['value' => 'original']));
        $current->instance('cleaner.log', new \ArrayObject());
        $manager = new CleanerManager($current, $snapshot, [
            'cleaners' => [RecordingCleaner::class, RecordingCleaner::class, ConfigCleaner::class],
        ]);

        $this->assertSame($current->make(RecordingCleaner::class), $current->make(RecordingCleaner::class));
        $manager->clean();

        $this->assertSame(['changed'], $current['cleaner.log']->getArrayCopy());
        $this->assertSame('original', $current['config']->get('value'));
    }

    /** @dataProvider controllerConfigurations */
    public function testControllerCleanupHonorsConfiguration(bool $lumen, bool $enabled, array $excluded, bool $preserved): void
    {
        $controller = new \stdClass();
        $route = new class($controller) {
            public $controller;

            public function __construct($controller)
            {
                $this->controller = $controller;
            }
        };
        $current = new Container();
        $current->instance('router', new class($route) {
            private $route;

            public function __construct($route)
            {
                $this->route = $route;
            }

            public function current()
            {
                return $this->route;
            }
        });
        $manager = new CleanerManager($current, new Container(), [
            'is_lumen' => $lumen,
            'destroy_controllers' => ['enable' => $enabled, 'excluded_list' => $excluded],
        ]);

        $manager->cleanControllers();

        if ($preserved) {
            $this->assertSame($controller, $route->controller);
        } else {
            $this->assertFalse(isset($route->controller));
        }
    }

    public static function controllerConfigurations(): array
    {
        return [
            'enabled' => [false, true, [], false],
            'disabled' => [false, false, [], true],
            'lumen' => [true, true, [], true],
            'excluded controller' => [false, true, [\stdClass::class], true],
            'other excluded controller' => [false, true, [\ArrayObject::class], false],
        ];
    }
}

class RecordingCleaner extends BaseCleaner
{
    public function clean()
    {
        $this->currentApp['cleaner.log'][] = $this->currentApp['config']->get('value');
    }
}
