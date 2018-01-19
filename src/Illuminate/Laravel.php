<?php

namespace Hhxsv5\LaravelS\Illuminate;


use Illuminate\Contracts\Http\Kernel;
use Illuminate\Cookie\CookieJar;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Laravel
{
    /**
     * @var Application $app
     */
    protected $app;

    /**
     * @var Kernel $kernel
     */
    protected $kernel;

    protected $conf = [];

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
        require_once $this->conf['rootPath'] . '/bootstrap/autoload.php';
    }

    protected function createApp()
    {
        $this->app = require $this->conf['rootPath'] . '/bootstrap/app.php';
    }

    protected function createKernel()
    {
        $this->kernel = $this->app->make(Kernel::class);
    }

    protected function setLaravel()
    {
        //Enables support for the _method request parameter to determine the intended HTTP method.
        Request::enableHttpMethodParameterOverride();
    }

    /**
     * Laravel handles request and return response
     * @param Request $request
     * @return Response|\Symfony\Component\HttpFoundation\Response
     */
    public function &handle(Request $request)
    {
        ob_start();

        $response = $this->kernel->handle($request);
        $content = $response->getContent();
        if (strlen($content) === 0 && ob_get_length() > 0) {
            $response->setContent(ob_get_contents());
        }

        ob_end_clean();

        $this->kernel->terminate($request, $response);
        return $response;
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