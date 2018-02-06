<?php
return [
    'listen_ip'     => env('LARAVELS_LISTEN_IP', '127.0.0.1'),
    'listen_port'   => env('LARAVELS_LISTEN_PORT', 5200),
    'enable_gzip'   => env('LARAVELS_ENABLE_GZIP', true),
    'server'        => env('LARAVELS_SERVER', 'LaravelS'),
    'handle_static' => env('LARAVELS_HANDLE_STATIC', false),
    'swoole'        => [
        'dispatch_mode' => 2,
        'max_request'   => 3000,
        'daemonize'     => 1,
        'pid_file'      => storage_path('laravels.pid'),
        'log_file'      => storage_path('logs/swoole-' . date('Y-m-d') . '.log'),
        'log_level'     => 4,
        'document_root' => base_path('public'),
    ],
];
