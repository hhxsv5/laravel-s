<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Illuminate\Cleaners\BaseCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\CleanerInterface;
use Hhxsv5\LaravelS\Illuminate\Cleaners\ConfigCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\ContainerCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\CookieCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\RequestCleaner;
use Illuminate\Container\Container;

class CleanerManager
{
    /**
     * @var Container
     */
    protected $currentApp;
    /**
     * @var Container
     */
    protected $snapshotApp;

    /**@var ReflectionApp */
    protected $reflectionApp;

    /**
     * All cleaners
     * @var CleanerInterface[]
     */
    protected $cleaners = [
        ContainerCleaner::class,
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
     * @var array<string,bool>
     */
    protected $whiteListControllers = [];

    /**
     * White list of controller prefixes to be excluded
     * @var array<string,bool>
     */
    protected $whiteListPrefixes = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * CleanerManager constructor.
     *
     * @param Container $currentApp
     * @param Container $snapshotApp
     * @param array $config
     */
    public function __construct(Container $currentApp, Container $snapshotApp, array $config)
    {
        $this->currentApp = $currentApp;
        $this->snapshotApp = $snapshotApp;
        $this->reflectionApp = new ReflectionApp($this->currentApp);
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
        foreach ($this->cleaners as $class) {
            $this->currentApp->singleton($class, function () use ($class) {
                $cleaner = new $class($this->currentApp, $this->snapshotApp);
                if (!($cleaner instanceof BaseCleaner)) {
                    throw new \InvalidArgumentException(sprintf(
                            '%s must extend the abstract class %s',
                            $cleaner,
                            BaseCleaner::class
                        )
                    );
                }
                return $cleaner;
            });
        }
    }

    /**
     * Clean app after request finished.
     */
    public function clean()
    {
        foreach ($this->cleaners as $class) {
            /**@var BaseCleaner $cleaner */
            $cleaner = $this->currentApp->make($class);
            $cleaner->clean();
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
     */
    public function cleanProviders()
    {
        $loadedProviders = $this->reflectionApp->loadedProviders();

        foreach ($this->providers as $provider) {
            if (class_exists($provider)) {
                if ($this->config['is_lumen']) {
                    unset($loadedProviders[get_class(new $provider($this->currentApp))]);
                }

                switch ($this->reflectionApp->registerMethodParameterCount()) {
                    case 1:
                        $this->currentApp->register($provider);
                        break;
                    case 2:
                        $this->currentApp->register($provider, true);
                        break;
                    case 3:
                        $this->currentApp->register($provider, [], true);
                        break;
                    default:
                        throw new \RuntimeException('The number of parameters of the register method is unknown.');
                }
            }
        }

        if ($this->config['is_lumen']) {
            $this->reflectionApp->setLoadedProviders($loadedProviders);
        }
    }

    /**
     * Register white list of controllers for cleaning.
     *
     * @param array $controllers
     */
    protected function registerCleanControllerWhiteList(array $controllers = [])
    {
        $this->whiteListControllers = [];
        $this->whiteListPrefixes = [];

        foreach ($controllers as $controller) {
            if (str_ends_with($controller, '*')) {
                $prefix = substr($controller, 0, -1);
                $this->whiteListPrefixes[$prefix] = true; // 使用关联数组
            } else {
                $this->whiteListControllers[$controller] = true; // 统一使用 true 作为值
            }
        }
    }

    /**
     * Check if controller should be excluded from cleaning
     *
     * @param string $controllerClass
     * @return bool
     */
    protected function shouldExcludeController(string $controllerClass): bool
    {
        // 1. 精确匹配检查（O(1)）
        if (isset($this->whiteListControllers[$controllerClass])) {
            return true;
        }

        // 2. 前缀匹配检查
        // 使用关联数组后，可以优化前缀匹配的性能
        foreach ($this->whiteListPrefixes as $prefix => $_) {
            if (strncmp($controllerClass, $prefix, strlen($prefix)) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clean controllers.
     */
    public function cleanControllers()
    {
        if ($this->config['is_lumen']) {
            return;
        }

        if (empty($this->config['destroy_controllers']['enable'])) {
            return;
        }

        /**@var \Illuminate\Routing\Route $route */
        $route = $this->currentApp['router']->current();
        if (!$route) {
            return;
        }

        if (isset($route->controller)) { // For Laravel 5.4+
            if (!$this->shouldExcludeController(get_class($route->controller))) {
                unset($route->controller);
            }
        } else {
            $reflection = new \ReflectionClass(get_class($route));
            if ($reflection->hasProperty('controller')) { // Laravel 5.3
                $controller = $reflection->getProperty('controller');
                $controller->setAccessible(true);
                $instance = $controller->getValue($route);
                if ($instance && !$this->shouldExcludeController(get_class($instance))) {
                    $controller->setValue($route, null);
                }
            }
        }
    }
}
