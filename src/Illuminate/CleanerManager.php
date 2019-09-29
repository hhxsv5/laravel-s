<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Illuminate\Cleaners\CleanerInterface;
use Hhxsv5\LaravelS\Illuminate\Cleaners\ConfigCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\CookieCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\RequestCleaner;
use Illuminate\Container\Container;

class CleanerManager
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * All cleaners
     * @var CleanerInterface[]
     */
    protected $cleaners = [
        ConfigCleaner::class,
        CookieCleaner::class,
        RequestCleaner::class,
    ];

    /**
     * Service providers to be cleaned up
     * @var array
     */
    protected $providers = [];

    /**
     * White list of controllers to be destroyed
     * @var array
     */
    protected $whiteListControllers = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * CleanerManager constructor.
     *
     * @param Container $app
     * @param array $config
     */
    public function __construct(Container $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->registerCleaners(isset($this->config['cleaners']) ? $this->config['cleaners'] : []);
        $this->registerCleanProviders(isset($config['register_providers']) ? $config['register_providers'] : []);
        $this->registerCleanControllerWhiteList(isset($this->config['destroy_controllers']['excluded_list']) ? $this->config['destroy_controllers']['excluded_list'] : []);
    }

    /**
     * Register singleton cleaners to application container.
     * @param array $cleaners
     */
    protected function registerCleaners(array $cleaners)
    {
        $this->cleaners = array_unique(array_merge($cleaners, $this->cleaners));
        foreach ($this->cleaners as $cleaner) {
            $this->app->singleton($cleaner, function () use ($cleaner) {
                if (!isset(class_implements($cleaner)[CleanerInterface::class])) {
                    throw new \InvalidArgumentException(sprintf(
                            '%s must implement the interface %s',
                            $cleaner,
                            CleanerInterface::class
                        )
                    );
                }
                return new $cleaner();
            });
        }
    }

    /**
     * Clean app after request finished.
     *
     * @param Container $snapshotApp
     */
    public function clean($snapshotApp)
    {
        foreach ($this->cleaners as $cleanerCls) {
            /**@var CleanerInterface $cleaner */
            $cleaner = $this->app->make($cleanerCls);
            $cleaner->clean($this->app, $snapshotApp);
        }
    }


    /**
     * Register providers for cleaning.
     *
     * @param array providers
     */
    protected function registerCleanProviders(array $providers = [])
    {
        $this->providers = $providers;
    }

    /**
     * Clean Providers.
     *
     * @param ReflectionApp $reflectionApp
     * @throws \ReflectionException
     */
    public function cleanProviders(ReflectionApp $reflectionApp)
    {
        $loadedProviders = $reflectionApp->loadedProviders();

        foreach ($this->providers as $provider) {
            if (class_exists($provider, false)) {
                if ($this->isLumen()) {
                    unset($loadedProviders[get_class(new $provider($this->app))]);
                }

                switch ($reflectionApp->registerMethodParameterCount()) {
                    case 1:
                        $this->app->register($provider);
                        break;
                    case 2:
                        $this->app->register($provider, true);
                        break;
                    case 3:
                        $this->app->register($provider, [], true);
                        break;
                    default:
                        throw new \RuntimeException('The number of parameters of the register method is unknown.');
                }
            }
        }

        if ($this->isLumen()) {
            $reflectionApp->setLoadedProviders($loadedProviders);
        }
    }

    /**
     * Register white list of controllers for cleaning.
     *
     * @param array providers
     */
    protected function registerCleanControllerWhiteList(array $controllers = [])
    {
        $controllers = array_unique($controllers);
        $this->whiteListControllers = array_combine($controllers, $controllers);
    }

    /**
     * Clean controllers.
     */
    public function cleanControllers()
    {
        if ($this->isLumen()) {
            return;
        }

        if (empty($this->config['destroy_controllers']['enable'])) {
            return;
        }

        /**@var \Illuminate\Routing\Route $route */
        $route = $this->app['router']->current();
        if (!$route) {
            return;
        }

        if (isset($route->controller)) { // For Laravel 5.4+
            if (empty($this->whiteListControllers) || !isset($this->whiteListControllers[get_class($route->controller)])) {
                unset($route->controller);
            }
        } else {
            $reflection = new \ReflectionClass(get_class($route));
            if ($reflection->hasProperty('controller')) { // For Laravel 5.3
                $controller = $reflection->getProperty('controller');
                $controller->setAccessible(true);
                if (empty($this->whiteListControllers) || (($instance = $controller->getValue($route)) && !isset($this->whiteListControllers[get_class($instance)]))) {
                    $controller->setValue($route, null);
                }
            }
        }
    }

    /**
     * Determine if is lumen.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return $this->config['is_lumen'];
    }
}
