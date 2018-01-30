<?php

namespace Hhxsv5\LaravelS\Illuminate;


use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Laravel
{
    protected $app;

    /**
     * @var Kernel $laravelKernel
     */
    protected $laravelKernel;

    protected $conf = [];

    protected static $staticBlackList = [
        '/index.php'  => 1,
        '/.htaccess'  => 1,
        '/web.config' => 1,
    ];

    public function __construct(array $conf = [])
    {
        $this->conf = $conf;
    }

    public function prepareLaravel()
    {
        $this->bootstrap();
        $this->createApp();
        $this->createKernel();
        $this->setLaravel();
    }

    protected function bootstrap()
    {
        $autoload = $this->conf['rootPath'] . '/bootstrap/autoload.php';
        // Lumen hasn't this autoload file
        if (file_exists($autoload)) {
            require_once $autoload;
        }
    }

    protected function createApp()
    {
        $this->app = require $this->conf['rootPath'] . '/bootstrap/app.php';
    }

    protected function createKernel()
    {
        if (!$this->conf['isLumen']) {
            $this->laravelKernel = $this->app->make(Kernel::class);
        }
    }

    protected function setLaravel()
    {
        // Enables support for the _method request parameter to determine the intended HTTP method.
        Request::enableHttpMethodParameterOverride();

        // Load configuration laravel.php manually for Lumen
        if ($this->conf['isLumen'] && file_exists($this->conf['rootPath'] . '/config/laravels.php')) {
            $this->app->configure('laravels');
        }
    }

    public function handleDynamic(Request $request)
    {
        ob_start();

        if ($this->conf['isLumen']) {
            $response = $this->app->dispatch($request);
            if ($response instanceof SymfonyResponse) {
                $content = $response->getContent();
            } else {
                $content = (string)$response;
            }
            if (!empty($this->app->middleware)) {
                $this->app->callTerminableMiddleware($response);
            }
        } else {
            $response = $this->laravelKernel->handle($request);
            $content = $response->getContent();
            $this->laravelKernel->terminate($request, $response);
        }

        // prefer content in response, secondly ob
        if (strlen($content) === 0 && ob_get_length() > 0) {
            $response->setContent(ob_get_contents());
        }

        ob_end_clean();

        $this->clean($request);

        return $response;
    }

    public function handleStatic(Request $request)
    {
        $uri = $request->getRequestUri();
        if (isset(self::$staticBlackList[$uri])) {
            return false;
        }

        // Locate the request file
        $publicPath = $this->conf['rootPath'] . '/public';
        $requestFile = $publicPath . $uri;
        if (is_file($requestFile)) {

        } elseif (is_dir($requestFile)) {
            $requestFile = rtrim($requestFile, '/');
            $found = false;
            foreach (['/index.html', '/index.htm'] as $index) {
                $tmpFile = $requestFile . $index;
                if (is_file($tmpFile)) {
                    $found = true;
                    $requestFile = $tmpFile;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        } else {
            return false;
        }

        $code = SymfonyResponse::HTTP_OK;
        $mtime = filemtime($requestFile);
        $modifiedSince = $request->header('if-modified-since');
        if ($modifiedSince !== null) {
            $modifiedSince = strtotime($modifiedSince);
            if ($modifiedSince !== false && $modifiedSince >= $mtime) {
                $code = SymfonyResponse::HTTP_NOT_MODIFIED;
            }
        }

        $maxAge = 24 * 3600;
        $rsp = new BinaryFileResponse($requestFile, $code);
        $rsp->setLastModified(new \DateTime(date('Y-m-d H:i:s', $mtime)));
        $rsp->setMaxAge($maxAge);
        $rsp->setPrivate();
        $rsp->setExpires(new \DateTime(date('Y-m-d H:i:s', time() + $maxAge)));
        return $rsp;

    }

    protected function clean(Request $request)
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

        // Clean laravel cookie queue
        if (class_exists('\Illuminate\Cookie\CookieJar', false)) {
            /**
             * @var \Illuminate\Cookie\CookieJar $cookies
             */
            $cookies = $this->app->make(\Illuminate\Cookie\CookieJar::class);
            foreach ($cookies->getQueuedCookies() as $name => $cookie) {
                $cookies->unqueue($name);
            }
        }

        //...
    }

    public function fireEvent($name, array $params = [])
    {
        $this->app->events->fire($name, $params);
    }

    public function bindSwoole($swoole)
    {
        $this->app->singleton('swoole', function () use ($swoole) {
            return $swoole;
        });
    }
}