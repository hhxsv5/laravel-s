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
    protected $app;
    protected $conf = [];

    public function __construct(array $conf = [])
    {
        $this->conf = $conf;
        $this->bootstrap();
        $this->createApp();
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
    }

    /**
     * Laravel handles request and return response
     * @param Request $request
     * @return Response
     */
    public function &handle(Request $request)
    {
        $kernel = $this->app->make(Kernel::class);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
        return $response;
    }

    protected function clean(Request $request)
    {
        if ($request->hasSession()) {
            $request->getSession()->clear();
        }

        // Clean laravel cookie queue
        $cookies = $this->app->make(CookieJar::class);
        foreach ($cookies->getQueuedCookies() as $name => $cookie) {
            $cookies->unqueue($name);
        }

        if ($this->app->isProviderLoaded(\Illuminate\Auth\AuthServiceProvider::class)) {
            $this->app->register(\Illuminate\Auth\AuthServiceProvider::class, [], true);
            Facade::clearResolvedInstance('auth');
        }

        //...
    }
}