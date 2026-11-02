<?php

require dirname(__DIR__) . '/vendor/autoload.php';

if (!extension_loaded('swoole')) {
    require __DIR__ . '/Support/SwooleHttp.php';
    define('SWOOLE_VERSION', '4.8.0');
}
