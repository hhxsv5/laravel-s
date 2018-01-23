<?php
return [
    'listen_ip'   => env('LARAVELS_LISTEN_IP', '0.0.0.0'),
    'listen_port' => env('LARAVELS_LISTEN_PORT', 8841),
    'enable_gzip' => extension_loaded('zlib') && env('LARAVELS_ENABLE_GZIP', 1),
    'server'      => env('LARAVELS_SERVER', 'LaravelS'),
    'swoole'      => [
        'dispatch_mode' => 2,
        'max_request'   => 3000,
        'daemonize'     => 1,
        'pid_file'      => storage_path('laravels.pid'),
        'log_file'      => storage_path('logs/swoole-' . date('Y-m-d') . '.log'),
        'log_level'     => 4,
    ],
];