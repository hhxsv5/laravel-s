<?php
/**
 * @see https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md  Chinese
 * @see https://github.com/hhxsv5/laravel-s/blob/master/Settings.md  English
 */
return [
    'listen_ip'      => env('LARAVELS_LISTEN_IP', '127.0.0.1'),
    'listen_port'    => env('LARAVELS_LISTEN_PORT', 5200),
    'enable_gzip'    => env('LARAVELS_ENABLE_GZIP', false),
    'server'         => env('LARAVELS_SERVER', 'LaravelS'),
    'handle_static'  => env('LARAVELS_HANDLE_STATIC', false),
    'inotify_reload' => [
        'enable'     => env('LARAVELS_INOTIFY_RELOAD', false),
        'file_types' => ['.php'],
        'log'        => true,
    ],
    'websocket'      => [
        'enable' => false,
        //'handler' => XxxHandler::class,
    ],
    'swoole'         => [
        'dispatch_mode' => 2,
        'reactor_num'   => \swoole_cpu_num() * 2,
        'worker_num'    => \swoole_cpu_num() * 2,
        //'task_worker_num' => \swoole_cpu_num() * 2,
        'max_request'   => 3000,
        'daemonize'     => 1,
        //'open_tcp_nodelay' => 1,
        'pid_file'      => storage_path('laravels.pid'),
        'log_file'      => storage_path(sprintf('logs/swoole-%s.log', date('Y-m'))),
        'log_level'     => 4,
        'document_root' => base_path('public'),

        /**
         * More settings of Swoole
         * @see https://wiki.swoole.com/wiki/page/274.html  Chinese
         * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration  English
         */
    ],
    'events'         => [
    ],
];
