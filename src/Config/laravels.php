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
        'enable'     => false,
        'file_types' => ['.php'],
    ],
    'swoole'         => [
        'dispatch_mode' => 2,
        'max_request'   => 3000,
        'daemonize'     => 1,
        'pid_file'      => storage_path('laravels.pid'),
        'log_file'      => storage_path('logs/swoole-' . date('Y-m-d') . '.log'),
        'log_level'     => 4,
        'document_root' => base_path('public'),
        /**
         * The other settings of Swoole like worker_num, backlog ...
         * @see https://wiki.swoole.com/wiki/page/274.html  Chinese
         * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration  English
         */
    ],
];
