<?php

namespace Hhxsv5\LaravelS\Illuminate\Cleaners;

use Illuminate\Support\Facades\Facade;

class LaravelAdminCleaner extends BaseCleaner
{
    public function clean()
    {
        \Encore\Admin\Admin::$script = [];
        \Encore\Admin\Admin::$deferredScript = [];
        \Encore\Admin\Admin::$headerJs = [];
        \Encore\Admin\Admin::$style = [];
        \Encore\Admin\Admin::$css = [];
        \Encore\Admin\Admin::$html = [];
        \Encore\Admin\Admin::$manifestData = [];
        \Encore\Admin\Admin::$extensions = [];
        \Encore\Admin\Admin::$js=[];
        \Encore\Admin\Admin::$headerJs=[];
        $this->currentApp->forgetInstance('Encore\Admin\Admin');
        Facade::clearResolvedInstance('Encore\Admin\Admin');
    }
}