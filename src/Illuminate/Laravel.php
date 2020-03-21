<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Laravel
{
    /**@var Container */
    protected $currentApp;

    /**@var Container */
    protected $snapshotApp;

    /**@var ReflectionApp */
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

    /**@var CleanerManager */
    protected $cleanerManager;

    public function __construct(array $conf = [])
    {
        $this->conf = $conf;

        // Merge $_ENV $_SERVER
        $server = isset($this->conf['_SERVER']) ? $this->conf['_SERVER'] : [];
        $env = isset($this->conf['_ENV']) ? $this->conf['_ENV'] : [];
        $this->rawGlobals['_SERVER'] = $_SERVER + $server;
        $this->rawGlobals['_ENV'] = $_ENV + $env;
    }

    public function prepareLaravel()
    {
        list($this->currentApp, $this->kernel) = $this->createAppKernel();

        $this->reflectionApp = new ReflectionApp($this->currentApp);

        $this->saveSnapshot();

        // Create cleaner manager
        $this->cleanerManager = new CleanerManager($this->currentApp, $this->snapshotApp, $this->conf);
    }

    protected function saveSnapshot()
    {
        $this->snapshotApp = clone $this->currentApp;

        $instances = $this->reflectionApp->instances();

        foreach ($instances as $key => $value) {
            $this->snapshotApp->offsetSet($key, is_object($value) ? clone $value : $value);
        }
    }

    protected function createAppKernel()
    {
        // Register autoload
        self::autoload($this->conf['root_path']);

        // Make kernel for Laravel
        $app = require $this->conf['root_path'] . '/bootstrap/app.php';
        $kernel = $this->isLumen() ? null : $app->make(HttpKernel::class);

        // Load all Configurations for Lumen
        if ($this->isLumen()) {
            $this->configureLumen($app);
        }

        // Boot
        if ($this->isLumen()) {
            if (method_exists($app, 'boot')) {
                $app->boot();
            }
        } else {
            $app->make(ConsoleKernel::class)->bootstrap();
        }

        return [$app, $kernel];
    }

    protected function configureLumen(Container $app)
    {
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

    /**
     * Record current request info, including start_time、request_url
     */
    private function recordWorkerStatus()
    {
        app('swoole')->statusTable->set(
            'worker:' . app('swoole')->worker_id,
            [
                'value' => json_encode([
                    'start_time' => ceil(microtime(true) * 1000),
                    'worker_id' => app('swoole')->worker_id,
                    'worker_pid' => app('swoole')->worker_pid,
                    'request_url' => app('request')->getPathInfo(),
                    'method' => app('request')->getMethod(),
                ])
            ]
        );
    }

    /**
     * Clear worker status from swoole table
     */
    private function clearWorkerStatus()
    {
        app('swoole')->statusTable->del('worker:' . app('swoole')->worker_id);
    }

    public function handleDynamic(IlluminateRequest $request)
    {
        $this->recordWorkerStatus();

        ob_start();

        if ($this->isLumen()) {
            $response = $this->currentApp->dispatch($request);
            if ($response instanceof SymfonyResponse) {
                $content = $response->getContent();
            } else {
                $content = (string)$response;
            }

            $this->reflectionApp->callTerminableMiddleware($response);
        } else {
            $response = $this->kernel->handle($request);
            $content = $response->getContent();
            $this->kernel->terminate($request, $response);
        }

        // prefer content in response, secondly ob
        if (!($response instanceof StreamedResponse) && strlen($content) === 0 && ob_get_length() > 0) {
            $response->setContent(ob_get_contents());
        }

        ob_end_clean();

        $this->clearWorkerStatus();

        return $response;
    }

    public function handleStatic(IlluminateRequest $request)
    {
        $uri = $request->getRequestUri();
        if (isset(self::$staticBlackList[$uri])) {
            return false;
        }
        $uri = urldecode($uri);

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

    public function clean()
    {
        $this->cleanerManager->clean();
        $this->cleanerManager->cleanControllers();
    }

    public function cleanProviders()
    {
        $this->cleanerManager->cleanProviders();
    }

    public function fireEvent($name, array $params = [])
    {
        $params[] = $this->currentApp;
        return method_exists($this->currentApp['events'], 'dispatch') ?
            $this->currentApp['events']->dispatch($name, $params) : $this->currentApp['events']->fire($name, $params);
    }

    public function bindRequest(IlluminateRequest $request)
    {
        $this->currentApp->instance('request', $request);
    }

    public function bindSwoole($swoole)
    {
        $this->currentApp->singleton('swoole', function () use ($swoole) {
            return $swoole;
        });
    }

    public function saveSession()
    {
        if (isset($this->currentApp['session'])) {
            $this->currentApp['session']->save();
        }
    }

    protected function isLumen()
    {
        return $this->conf['is_lumen'];
    }
}
