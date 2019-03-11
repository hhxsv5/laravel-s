<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Illuminate\Cleaners\CleanerInterface;
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

    /**@var HttpKernel */
    protected $kernel;

    /**@var array */
    protected $snapshots = [];

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

    public function __construct(array $conf = [])
    {
        $this->conf = $conf;
    }

    public function prepareLaravel()
    {
        static::autoload($this->conf['root_path']);
        $this->createApp();
        $this->createKernel();
        $this->setLaravel();
        $this->loadAllConfigurations();
        $this->bootstrap();
        $this->saveSnapshots();
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

    protected function createApp()
    {
        $this->app = require $this->conf['root_path'] . '/bootstrap/app.php';
    }

    protected function createKernel()
    {
        if (!$this->conf['is_lumen']) {
            $this->kernel = $this->app->make(HttpKernel::class);
        }
    }

    protected function setLaravel()
    {
        // Load configuration laravel.php manually for Lumen
        if ($this->conf['is_lumen'] && file_exists($this->conf['root_path'] . '/config/laravels.php')) {
            $this->app->configure('laravels');
        }

        $server = isset($this->conf['_SERVER']) ? $this->conf['_SERVER'] : [];
        $env = isset($this->conf['_ENV']) ? $this->conf['_ENV'] : [];
        $this->rawGlobals['_SERVER'] = array_merge($_SERVER, $server);
        $this->rawGlobals['_ENV'] = array_merge($_ENV, $env);
    }

    protected function bootstrap()
    {
        if ($this->conf['is_lumen']) {
            if (method_exists($this->app, 'boot')) {
                $this->app->boot();
            }
        } else {
            $this->app->make(ConsoleKernel::class)->bootstrap();
        }

        foreach ($this->conf['cleaners'] as $cleanerCls) {
            $this->app->singleton($cleanerCls, function ($app) use ($cleanerCls) {
                if (!isset(class_implements($cleanerCls)[CleanerInterface::class])) {
                    throw new \Exception(sprintf(
                            '%s must implement the interface %s',
                            $cleanerCls,
                            CleanerInterface::class
                        )
                    );
                }
                return new $cleanerCls();
            });
        }
    }

    public function loadAllConfigurations()
    {
        if (!$this->conf['is_lumen']) {
            return;
        }

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
            $this->app->configure($key);
        }
    }

    protected function saveSnapshots()
    {
        $this->snapshots['config'] = $this->app['config']->all();
    }

    protected function applySnapshots()
    {
        $this->app['config']->set($this->snapshots['config']);
        if (isset($this->app['cookie'])) {
            foreach ($this->app['cookie']->getQueuedCookies() as $name => $cookie) {
                $this->app['cookie']->unqueue($name);
            }
        }
    }

    public function getRawGlobals()
    {
        return $this->rawGlobals;
    }

    public function handleDynamic(IlluminateRequest $request)
    {
        $this->applySnapshots();

        ob_start();

        if ($this->conf['is_lumen']) {
            $response = $this->app->dispatch($request);
            if ($response instanceof SymfonyResponse) {
                $content = $response->getContent();
            } else {
                $content = (string)$response;
            }

            $laravelReflect = new \ReflectionObject($this->app);
            $middleware = $laravelReflect->getProperty('middleware');
            $middleware->setAccessible(true);
            if (!empty($middleware->getValue($this->app))) {
                $callTerminableMiddleware = $laravelReflect->getMethod('callTerminableMiddleware');
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
            $laravelReflect = new \ReflectionObject($this->app);
            $loadedProviders = $laravelReflect->getProperty('loadedProviders');
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
            $cleaner->clean($this->app);
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
