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
     * @var array
     */
    protected $providers;

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

        $cleaners = isset($this->config['cleaners']) ? $this->config['cleaners'] : [];
        $this->addCleaner($cleaners);

        $this->registerCleaners();
        $this->registerCleanProviders($config['register_providers']);
    }

    /**
     * Add cleaners.
     *
     * @param CleanerInterface[]|CleanerInterface $cleaner
     */
    protected function addCleaner($cleaner)
    {
        $cleaners = is_array($cleaner) ? $cleaner : [$cleaner];

        $this->cleaners = array_unique(array_merge($cleaners, $this->cleaners));
    }

    /**
     * Register singleton cleaners to application container.
     */
    protected function registerCleaners()
    {
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
     * Register providers for cleaning.
     *
     * @param array $registerProviders
     */
    protected function registerCleanProviders($registerProviders = [])
    {
        $this->providers = $registerProviders;
    }

    /**
     * Clean Providers.
     *
     * @param ReflectionApp $reflectionApp
     * @throws \ReflectionException
     */
    protected function cleanProviders(ReflectionApp $reflectionApp)
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
     * Determine if is lumen.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return $this->config['is_lumen'];
    }

    /**
     * Clean app after request finished.
     *
     * @param Container $snapshotApp
     * @param ReflectionApp $reflectionApp
     * @throws \ReflectionException
     */
    public function clean($snapshotApp, ReflectionApp $reflectionApp)
    {
        foreach ($this->cleaners as $cleanerCls) {
            /**@var CleanerInterface $cleaner */
            $cleaner = $this->app->make($cleanerCls);
            $cleaner->clean($this->app, $snapshotApp);
        }

        $this->cleanProviders($reflectionApp);
    }
}
