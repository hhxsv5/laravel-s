<?php

namespace Hhxsv5\LaravelS\Illuminate;

class ReflectionApp
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * @var \ReflectionObject
     */
    protected $reflectionApp;

    /**
     * ReflectionApp constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     */
    public function __construct($app)
    {
        $this->app = $app;

        $this->reflectionApp = new \ReflectionObject($app);
    }

    /**
     * Get all bindings from application container.
     *
     * @return array
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
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function callTerminableMiddleware($response)
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
     */
    public function registerMethodParameterCount()
    {
        return $this->reflectionApp->getMethod('register')->getNumberOfParameters();
    }

    /**
     * Get 'loadedProviders' of application container.
     *
     * @return array
     */
    public function loadedProviders()
    {
        $loadedProviders = $this->reflectLoadedProviders();
        $loadedProviders->setAccessible(true);

        return $loadedProviders->getValue($this->app);
    }

    /**
     * Set 'loadedProviders' of application container.
     *
     * @param array $loadedProviders
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
     */
    protected function reflectLoadedProviders()
    {
        return $this->reflectionApp->getProperty('loadedProviders');
    }
}
