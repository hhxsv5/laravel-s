<?php

namespace Hhxsv5\LaravelS\Laravel;


use Hhxsv5\LaravelS\LaravelS;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

class Laravel
{
    public function __construct(array $laravelConf = [])
    {

    }


    protected function bootstrap()
    {
        define('LARAVEL_START', microtime(true));
        require __DIR__ . '/../vendor/autoload.php';

        $compiledPath = __DIR__ . '/cache/compiled.php';

        if (file_exists($compiledPath)) {
            require $compiledPath;
        }
    }

    public function run(LaravelS $s)
    {
        $this->bootstrap();

        $app = require_once __DIR__ . '/../bootstrap/app.php';

        $kernel = $app->make(Kernel::class);

        $response = $kernel->handle(
            $request = Request::capture()
        );

        $response->send();

        $kernel->terminate($request, $response);

    }


}