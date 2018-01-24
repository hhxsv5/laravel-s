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

    protected $isLumen = false;
    protected $publicPath;

    protected static $staticBlackList = [
        'index.php'  => 1,
        '.htaccess'  => 1,
        'web.config' => 1,
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
        $this->publicPath = $this->app->make('path.public');
    }

    protected function bootstrap()
    {
        $autoload = $this->conf['rootPath'] . '/bootstrap/autoload.php';
        // Lumen hasn't this autoload file
        if (file_exists($autoload)) {
            require_once $autoload;
            $this->isLumen = false;
        } else {
            $this->isLumen = true;
        }
    }

    protected function createApp()
    {
        $this->app = require $this->conf['rootPath'] . '/bootstrap/app.php';
    }

    protected function createKernel()
    {
        if (!$this->isLumen) {
            $this->laravelKernel = $this->app->make(Kernel::class);
        }
    }

    protected function setLaravel()
    {
        //Enables support for the _method request parameter to determine the intended HTTP method.
        Request::enableHttpMethodParameterOverride();
    }

    public function handleDynamic(Request $request)
    {
        ob_start();

        if ($this->isLumen) {
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
        return $response;
    }

    public function handleStatic(Request $request)
    {
        $uri = $request->getRequestUri();
        if (isset(self::$staticBlackList[$uri])) {
            return false;
        }

        $file = $this->publicPath . $uri;
        if (is_file($file)) {
            return new BinaryFileResponse($file, 200);
        }
        return false;
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
}