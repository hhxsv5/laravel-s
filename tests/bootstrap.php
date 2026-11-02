<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Use the API declarations from swoole/ide-helper as PHPUnit mock targets.
// No Swoole server or extension is needed to run the unit suite.
spl_autoload_register(function ($class) {
    if (strpos($class, 'Swoole\\') !== 0) {
        return;
    }
    $path = dirname(__DIR__) . '/vendor/swoole/ide-helper/src/swoole/'
        . str_replace('\\', '/', $class) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

// Only the defaults used in the mocked HTTP API are needed. Loading the full
// constants file would redeclare platform constants from pcntl and sockets.
foreach ([
    'SWOOLE_VERSION' => '5.0.0',
    'SWOOLE_HTTP2_ERROR_NO_ERROR' => 0,
    'SWOOLE_WEBSOCKET_CLOSE_NORMAL' => 1000,
    'SWOOLE_WEBSOCKET_OPCODE_TEXT' => 1,
    'SWOOLE_WEBSOCKET_FLAG_FIN' => 1,
] as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}
