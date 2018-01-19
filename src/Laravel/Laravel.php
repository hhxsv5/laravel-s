<?php

namespace Hhxsv5\LaravelS\Laravel;


use Illuminate\Contracts\Http\Kernel;
use Illuminate\Cookie\CookieJar;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;

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
        $this->bootstrap();
        $this->createApp();
        $this->createKernel();
    }

    protected function bootstrap()
    {
        require_once $this->conf['rootPath'] . '/bootstrap/autoload.php';
    }

    protected function createApp()
    {
        $this->app = new Application($this->conf['rootPath']);
        $rootNamespace = $this->app->getNamespace();
        $rootNamespace = trim($rootNamespace, '\\');

        $this->app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            "\\{$rootNamespace}\\Http\\Kernel"
        );

        $this->app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            "\\{$rootNamespace}\\Console\\Kernel"
        );

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            "\\{$rootNamespace}\\Exceptions\\Handler"
        );

//        $this->app->bootstrapWith([
//            'Illuminate\Foundation\Bootstrap\DetectEnvironment',
//            'Illuminate\Foundation\Bootstrap\LoadConfiguration',
//            'Illuminate\Foundation\Bootstrap\ConfigureLogging',
//            'Illuminate\Foundation\Bootstrap\HandleExceptions',
//            'Illuminate\Foundation\Bootstrap\RegisterFacades',
//            'Illuminate\Foundation\Bootstrap\SetRequestForConsole',
//            'Illuminate\Foundation\Bootstrap\RegisterProviders',
//            'Illuminate\Foundation\Bootstrap\BootProviders',
//        ]);
    }

    protected function createKernel()
    {
        $this->kernel = $this->app->make(Kernel::class);
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