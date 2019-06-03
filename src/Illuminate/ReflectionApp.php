<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\Response;

class ReflectionApp
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var \ReflectionObject
     */
    protected $reflectionApp;

    /**
     * ReflectionApp constructor.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;

        $this->reflectionApp = new \ReflectionObject($app);
    }

    /**
     * Get all bindings from application container.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function instances()
    {
        $instances = $this->reflectionApp->getProperty('instances');
        $instances->setAccessible(true);
        $instances = array_merge($this->app->getBindings(), $instances->getValue($this->app));

        return $instances;
    }

    /**
     * Call terminable middleware of Lumen.
     *
     * @param Response $response
     * @throws \ReflectionException
     */
    public function callTerminableMiddleware(Response $response)
    {
        $middleware = $this->reflectionApp->getProperty('middleware');
        $middleware->setAccessible(true);

        if (!empty($middleware->getValue($this->app))) {
            $callTerminableMiddleware = $this->reflectionApp->getMethod('callTerminableMiddleware');
            $callTerminableMiddleware->setAccessible(true);
            $callTerminableMiddleware->invoke($this->app, $response);
        }
    }

    /**
     * The parameter count of 'register' method in app container.
     *
     * @return int
     * @throws \ReflectionException
     */
    public function registerMethodParameterCount()
    {
        return $this->reflectionApp->getMethod('register')->getNumberOfParameters();
    }

    /**
     * Get 'loadedProviders' of application container.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function loadedProviders()
    {
        $loadedProviders = $this->reflectLoadedProviders();
        return $loadedProviders->getValue($this->app);
    }

    /**
     * Set 'loadedProviders' of application container.
     *
     * @param array $loadedProviders
     * @throws \ReflectionException
     */
    public function setLoadedProviders(array $loadedProviders)
    {
        $reflectLoadedProviders = $this->reflectLoadedProviders();
        $reflectLoadedProviders->setValue($this->app, $loadedProviders);
    }

    /**
     * Get the reflect loadedProviders of application container.
     *
     * @return \ReflectionProperty
     * @throws \ReflectionException
     */
    protected function reflectLoadedProviders()
    {
        $loadedProviders = $this->reflectionApp->getProperty('loadedProviders');
        $loadedProviders->setAccessible(true);
        return $loadedProviders;
    }
}
