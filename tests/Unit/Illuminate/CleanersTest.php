<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Illuminate;

use Hhxsv5\LaravelS\Illuminate\Cleaners\ConfigCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\ContainerCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\CookieCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\RequestCleaner;
use Hhxsv5\LaravelS\Tests\TestCase;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Cookie\CookieJar;
use Illuminate\Support\Facades\Facade;

class CleanersTest extends TestCase
{
    private $facadeApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->facadeApp = Facade::getFacadeApplication();
        Facade::clearResolvedInstances();
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->facadeApp);
        parent::tearDown();
    }

    public function testRestoresConfigurationFromSnapshot(): void
    {
        $snapshot = new Container();
        $snapshot->instance('config', new Repository(['app' => ['name' => 'original', 'debug' => false]]));
        $current = new Container();
        $current->instance('config', new Repository(['app' => ['name' => 'modified', 'debug' => true, 'temporary' => 'value']]));

        (new ConfigCleaner($current, $snapshot))->clean();

        $this->assertSame($snapshot['config']->all(), $current['config']->all());
        $this->assertNotSame($snapshot['config'], $current['config']);
        $current['config']->set('app.name', 'next request');
        $this->assertSame('original', $snapshot['config']->get('app.name'));
    }

    public function testClearsQueuedCookiesBetweenRequests(): void
    {
        $current = new Container();
        $jar = new CookieJar();
        $current->instance('cookie', $jar);
        $cleaner = new CookieCleaner($current, new Container());
        $jar->queue($jar->make('session', 'secret'));

        $cleaner->clean();
        $this->assertSame([], $jar->getQueuedCookies());

        $jar->queue($jar->make('next', 'value'));
        $cleaner->clean();
        $this->assertSame([], $jar->getQueuedCookies());
        $this->assertSame($jar, $current['cookie']);
    }

    public function testCookieCleanerAllowsApplicationsWithoutCookieService(): void
    {
        $current = new Container();
        (new CookieCleaner($current, new Container()))->clean();
        $this->assertFalse($current->bound('cookie'));
    }

    public function testRequestCleanerDropsContainerAndFacadeInstances(): void
    {
        $current = new Container();
        foreach (['request', 'url'] as $service) {
            $current->singleton($service, function () {
                return new \stdClass();
            });
        }
        $preserved = new \stdClass();
        $current->instance('other', $preserved);
        Facade::setFacadeApplication($current);
        $oldRequest = RequestFacade::getFacadeRoot();
        $oldUrl = UrlFacade::getFacadeRoot();

        (new RequestCleaner($current, new Container()))->clean();

        $this->assertNotSame($oldRequest, $current->make('request'));
        $this->assertNotSame($oldUrl, $current->make('url'));
        $this->assertSame($current->make('request'), RequestFacade::getFacadeRoot());
        $this->assertSame($current->make('url'), UrlFacade::getFacadeRoot());
        $this->assertSame($preserved, $current->make('other'));
    }

    public function testContainerCleanerRemovesRebindingCallbacks(): void
    {
        $current = new Container();
        $current->instance('request', new \stdClass());
        $calls = 0;
        $current->rebinding('request', function () use (&$calls) {
            $calls++;
        });
        $current->instance('request', new \stdClass());
        $this->assertSame(1, $calls);

        (new ContainerCleaner($current, new Container()))->clean();
        $current->instance('request', new \stdClass());

        $this->assertSame(1, $calls);
    }

    public function testContainerCleanerRestoresLumenRouteDefault(): void
    {
        $current = new class extends Container {
            public $currentRoute = [];
        };
        $current->currentRoute = ['GET', '/previous-request'];

        (new ContainerCleaner($current, new Container()))->clean();

        $this->assertSame([], $current->currentRoute);
    }
}

class RequestFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}

class UrlFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'url';
    }
}
