<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Support\Facades\Facade;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Laravel
{
    protected $app;

    /**
     * @var HttpKernel $kernel
     */
    protected $kernel;

    protected static $snapshotKeys = ['config', 'cookie', 'auth', /*'auth.password'*/];

    /**
     * @var array $snapshots
     */
    protected $snapshots = [];

    protected $conf = [];

    protected static $staticBlackList = [
        '/index.php'  => 1,
        '/.htaccess'  => 1,
        '/web.config' => 1,
    ];

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
        $this->consoleKernelBootstrap();
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

    protected function consoleKernelBootstrap()
    {
        if (!$this->conf['is_lumen']) {
            $this->app->make(ConsoleKernel::class)->bootstrap();
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

    public function reRegisterServiceProvider($providerCls, array $clearFacades = [], $force = false)
    {
        if (class_exists($providerCls, false) || $force) {
            if ($this->conf['is_lumen']) {
                $laravelReflect = new \ReflectionObject($this->app);
                $loadedProviders = $laravelReflect->getProperty('loadedProviders');
                $loadedProviders->setAccessible(true);
                $oldLoadedProviders = $loadedProviders->getValue($this->app);
                unset($oldLoadedProviders[get_class(new $providerCls($this->app))]);
                $loadedProviders->setValue($this->app, $oldLoadedProviders);
            }
            foreach ($clearFacades as $facade) {
                Facade::clearResolvedInstance($facade);
            }
            $this->app->register($providerCls, [], true);
        }
    }

    public function cleanRequest(IlluminateRequest $request)
    {
        // Clean laravel session
        if ($request->hasSession()) {
            $session = $request->getSession();
            if (method_exists($session, 'clear')) {
                $session->clear();
            } elseif (method_exists($session, 'flush')) {
                $session->flush();
            }
            // TODO: clear session for other versions
        }

        // Re-register auth
        //$this->reRegisterServiceProvider('\Illuminate\Auth\AuthServiceProvider', ['auth', 'auth.driver']);
        //$this->reRegisterServiceProvider('\Illuminate\Auth\Passwords\PasswordResetServiceProvider', ['auth.password']);

        // Re-register passport
        $this->reRegisterServiceProvider('\Laravel\Passport\PassportServiceProvider');

        // Re-register some singleton providers
        foreach ($this->conf['register_providers'] as $provider) {
            $this->reRegisterServiceProvider($provider);
        }

        // Clear request
        $this->app->forgetInstance('request');
        Facade::clearResolvedInstance('request');

        //...
    }

    public function fireEvent($name, array $params = [])
    {
        $params[] = $this->app;
        return $this->app->events->fire($name, $params);
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

    public function resetSession()
    {
        if (!empty($this->app['session'])) {
            $reflection = new \ReflectionObject($this->app['session']);
            $drivers = $reflection->getProperty('drivers');
            $drivers->setAccessible(true);
            $drivers->setValue($this->app['session'], []);
        }
    }

    public function saveSession()
    {
        if (!empty($this->app['session'])) {
            $this->app['session']->save();
        }
    }
}
