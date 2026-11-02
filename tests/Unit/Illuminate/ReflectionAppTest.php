<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Illuminate;

use Hhxsv5\LaravelS\Illuminate\ReflectionApp;
use Hhxsv5\LaravelS\Tests\TestCase;
use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\Response;

class ReflectionAppTest extends TestCase
{
    public function testMergesBindingsAndResolvedInstancesWithoutResolvingServices(): void
    {
        $app = new Container();
        $app->bind('unresolved', function () {
            throw new \LogicException('Listing bindings must not instantiate services');
        });
        $app->singleton('shared', function () {
            return new \stdClass();
        });
        $shared = $app->make('shared');
        $instance = new \stdClass();
        $app->instance('instance', $instance);

        $instances = (new ReflectionApp($app))->instances();

        $this->assertSame($app->getBindings()['unresolved'], $instances['unresolved']);
        $this->assertSame($shared, $instances['shared']);
        $this->assertSame($instance, $instances['instance']);
    }

    public function testReadsAndReplacesLoadedProviders(): void
    {
        $app = new class extends Container {
            protected $loadedProviders = ['OriginalProvider' => true];

            public function register($provider, $force = false) {}
        };
        $reflection = new ReflectionApp($app);

        $this->assertSame(['OriginalProvider' => true], $reflection->loadedProviders());
        $reflection->setLoadedProviders(['NewProvider' => true]);
        $this->assertSame(['NewProvider' => true], $reflection->loadedProviders());
        $this->assertSame(2, $reflection->registerMethodParameterCount());
    }

    public function testCallsLumenTerminableMiddlewareOnlyWhenConfigured(): void
    {
        $app = new class extends Container {
            protected $middleware = [];
            public $responses = [];

            public function enableMiddleware(): void
            {
                $this->middleware = ['terminable'];
            }

            protected function callTerminableMiddleware($response): void
            {
                $this->responses[] = $response;
            }
        };
        $reflection = new ReflectionApp($app);
        $response = new Response('done');

        $reflection->callTerminableMiddleware($response);
        $this->assertSame([], $app->responses);

        $app->enableMiddleware();
        $reflection->callTerminableMiddleware($response);
        $this->assertSame([$response], $app->responses);
    }
}
