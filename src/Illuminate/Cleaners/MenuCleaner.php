<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17
 * Time: 9:22
 */

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class MenuCleaner implements CleanerInterface
{
    public function clean(Container $app, Container $snapshot)
    {
        $app->forgetInstance('Lavary\Menu\Menu');
        Facade::clearResolvedInstance('Lavary\Menu\Menu');
    }
}
