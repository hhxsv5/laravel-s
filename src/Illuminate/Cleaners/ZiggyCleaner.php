<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Tightenco\Ziggy\BladeRouteGenerator;

class ZiggyCleaner extends BaseCleaner
{
    public function clean()
    {
        BladeRouteGenerator::$generated = false;
    }
}