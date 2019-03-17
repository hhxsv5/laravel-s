<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Illuminate\Cleaners\CleanerInterface;
use Hhxsv5\LaravelS\Illuminate\Cleaners\ConfigCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\CookieCleaner;
use Hhxsv5\LaravelS\Illuminate\Cleaners\RequestCleaner;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Laravel
{
    /**@var Container */
    protected $app;

    /**@var Container */
    protected $snapshotApp;

    /**@var \ReflectionObject */
    protected $reflectionApp;

    /**@var HttpKernel */
    protected $kernel;

    /**@var array */
    protected $conf = [];

    /**@var array */
    protected static $staticBlackList = [
        '/index.php'  => 1,
        '/.htaccess'  => 1,
        '/web.config' => 1,
    ];

    /**@var array */
    private $rawGlobals = [];

    /**@var array */
    protected static $defaultCleaners = [
        ConfigCleaner::class,
        CookieCleaner::class,
        RequestCleaner::class,
    ];

    public function __construct(array $conf = [])
    {
        $this->conf = $conf;

        // Merge $_ENV $_SERVER
        $server = isset($this->conf['_SERVER']) ? $this->conf['_SERVER'] : [];
        $env = isset($this->conf['_ENV']) ? $this->conf['_ENV'] : [];
        $this->rawGlobals['_SERVER'] = array_merge($_SERVER, $server);
        $this->rawGlobals['_ENV'] = array_merge($_ENV, $env);

        // Add default cleaners
        $this->conf['cleaners'] = isset($this->conf['cleaners']) ? $this->conf['cleaners'] : [];
        $this->conf['cleaners'] = array_unique(array_merge($this->conf['cleaners'], self::$defaultCleaners));
    }

    public function prepareLaravel()
    {
        list($this->app, $this->kernel) = $this->createAppKernel();
        $this->snapshotApp = clone $this->app;

        $this->reflectionApp = new \ReflectionObject($this->app);

        // Save snapshots for app
        $instances = $this->reflectionApp->getProperty('instances');
        $instances->setAccessible(true);
        $instances = array_merge($this->app->getBindings(), $instances->getValue($this->app));
        foreach ($instances as $key => $value) {
            $this->snapshotApp->offsetSet($key, is_object($value) ? clone $value : $value);
        }
    }

    public function createAppKernel()
    {
        // Register autoload
        self::autoload($this->conf['root_path']);

        // Make kernel for Laravel
        $kernel = null;
        $app = require $this->conf['root_path'] . '/bootstrap/app.php';
        if (!$this->conf['is_lumen']) {
            $kernel = $app->make(HttpKernel::class);
        }

        // Load all Configurations for Lumen
        if ($this->conf['is_lumen']) {
            $cfgPaths = [
                // Framework default configuration
                $this->conf['root_path'] . '/vendor/laravel/lumen-framework/config/',
                // App configuration
                $this->conf['root_path'] . '/config/',
            ];
            $keys = [];
            foreach ($cfgPaths as $cfgPath) {
                $configs = (array)glob($cfgPath . '*.php');
                foreach ($configs as $config) {
                    $config = substr(basename($config), 0, -4);
                    $keys[$config] = $config;
                }
            }
            foreach ($keys as $key) {
                $app->configure($key);
            }
        }

        // Boot
        if ($this->conf['is_lumen']) {
            if (method_exists($app, 'boot')) {
                $app->boot();
            }
        } else {
            $app->make(ConsoleKernel::class)->bootstrap();
        }

        // Bind singleton cleaners
        foreach ($this->conf['cleaners'] as $cleaner) {
            $app->singleton($cleaner, function ($app) use ($cleaner) {
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

        return [$app, $kernel];
    }

    public static function autoload($rootPath)
    {
        $autoload = $rootPath . '/bootstrap/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        } else {
            require_once $rootPath . '/vendor/autoload.php';
        }
    }

    public function getRawGlobals()
    {
        return $this->rawGlobals;
    }

    public function handleDynamic(IlluminateRequest $request)
    {
        ob_start();

        if ($this->conf['is_lumen']) {
            $response = $this->app->dispatch($request);
            if ($response instanceof SymfonyResponse) {
                $content = $response->getContent();
            } else {
                $content = (string)$response;
            }

            $middleware = $this->reflectionApp->getProperty('middleware');
            $middleware->setAccessible(true);
            if (!empty($middleware->getValue($this->app))) {
                $callTerminableMiddleware = $this->reflectionApp->getMethod('callTerminableMiddleware');
                $callTerminableMiddleware->setAccessible(true);
                $callTerminableMiddleware->invoke($this->app, $response);
            }
        } else {
            $response = $this->kernel->handle($request);
            $content = $response->getContent();
            $this->kernel->terminate($request, $response);
        }

        // prefer content in response, secondly ob
        if (strlen($content) === 0 && ob_get_length() > 0) {
            $response->setContent(ob_get_contents());
        }

        ob_end_clean();

        return $response;
    }

    public function handleStatic(IlluminateRequest $request)
    {
        $uri = $request->getRequestUri();
        if (isset(self::$staticBlackList[$uri])) {
            return false;
        }

        $publicPath = $this->conf['static_path'];
        $requestFile = $publicPath . $uri;
        if (is_file($requestFile)) {
            return $this->createStaticResponse($requestFile, $request);
        } elseif (is_dir($requestFile)) {
            $indexFile = $this->lookupIndex($requestFile);
            if ($indexFile === false) {
                return false;
            } else {
                return $this->createStaticResponse($indexFile, $request);
            }
        } else {
            return false;
        }
    }

    protected function lookupIndex($folder)
    {
        $folder = rtrim($folder, '/') . '/';
        foreach (['index.html', 'index.htm'] as $index) {
            $tmpFile = $folder . $index;
            if (is_file($tmpFile)) {
                return $tmpFile;
            }
        }
        return false;
    }

    public function createStaticResponse($requestFile, IlluminateRequest $request)
    {
        $response = new BinaryFileResponse($requestFile);
        $response->prepare($request);
        $response->isNotModified($request);
        return $response;
    }

    protected function cleanProviders(array $providers, $force = false)
    {
        if ($this->conf['is_lumen']) {
            $loadedProviders = $this->reflectionApp->getProperty('loadedProviders');
            $loadedProviders->setAccessible(true);
            $oldLoadedProviders = $loadedProviders->getValue($this->app);
        }

        foreach ($providers as $provider) {
            if ($force || class_exists($provider, false)) {
                if ($this->conf['is_lumen']) {
                    unset($oldLoadedProviders[get_class(new $provider($this->app))]);
                }
                $this->app->register($provider, [], true);
            }
        }

        if ($this->conf['is_lumen']) {
            $loadedProviders->setValue($this->app, $oldLoadedProviders);
        }
    }

    public function clean()
    {
        foreach ($this->conf['cleaners'] as $cleanerCls) {
            /**@var CleanerInterface $cleaner */
            $cleaner = $this->app->make($cleanerCls);
            $cleaner->clean($this->app, $this->snapshotApp);
        }

        $this->cleanProviders($this->conf['register_providers']);
    }

    public function fireEvent($name, array $params = [])
    {
        $params[] = $this->app;
        return method_exists($this->app['events'], 'dispatch') ?
            $this->app['events']->dispatch($name, $params) : $this->app['events']->fire($name, $params);
    }

    public function bindRequest(IlluminateRequest $request)
    {
        $this->app->instance('request', $request);
    }

    public function bindSwoole($swoole)
    {
        $this->app->singleton('swoole', function () use ($swoole) {
            return $swoole;
        });
    }

    public function make($abstract, array $parameters = [])
    {
        return $this->app->make($abstract, $parameters);
    }

    public function saveSession()
    {
        if ($this->app->offsetExists('session')) {
            $this->app['session']->save();
        }
    }
}
