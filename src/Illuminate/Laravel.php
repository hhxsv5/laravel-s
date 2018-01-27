<?php

namespace Hhxsv5\LaravelS\Illuminate;


use Illuminate\Contracts\Http\Kernel;
use Illuminate\Cookie\CookieJar;
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

        $file = $this->conf['rootPath'] . '/public' . $uri;
        if (!is_readable($file)) {
            return false;
        }

        $code = SymfonyResponse::HTTP_OK;
        $mtime = filemtime($file);
        $modifiedSince = $request->header('if-modified-since');
        if ($modifiedSince !== null) {
            $modifiedSince = strtotime($modifiedSince);
            if ($modifiedSince !== false && $modifiedSince >= $mtime) {
                $code = SymfonyResponse::HTTP_NOT_MODIFIED;
            }
        }

        $maxAge = 24 * 3600;
        $rsp = new BinaryFileResponse($file, $code);
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
            $request->getSession()->clear();
        }

        // Clean laravel cookie queue
        /**
         * @var CookieJar $cookies
         */
        $cookies = $this->app->make(CookieJar::class);
        foreach ($cookies->getQueuedCookies() as $name => $cookie) {
            $cookies->unqueue($name);
        }

        //...
    }

    public function fireEvent($name, array $params = [])
    {
        $this->app->events->fire($name, $params);
    }
}