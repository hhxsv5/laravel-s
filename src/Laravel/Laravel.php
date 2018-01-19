<?php

namespace Hhxsv5\LaravelS\Laravel;


use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Laravel
{
    protected $app;

    public function __construct(array $laravelConf = [])
    {
        $this->bootstrap();
    }

    protected function bootstrap()
    {
        define('LARAVEL_START', microtime(true));
        require __DIR__ . '/../vendor/autoload.php';

        $compiledPath = __DIR__ . '/cache/compiled.php';

        if (file_exists($compiledPath)) {
            require $compiledPath;
        }

        $this->app = require_once __DIR__ . '/../bootstrap/app.php';
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
}