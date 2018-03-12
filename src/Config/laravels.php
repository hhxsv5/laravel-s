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
        //'handler' => XxxWebsocketHandler::class,
    ],
    'swoole'         => [
        'daemonize'          => env('LARAVELS_DAEMONIZE', true),
        'dispatch_mode'      => 1,
        'reactor_num'        => function_exists('\swoole_cpu_num') ? \swoole_cpu_num() * 2 : 4,
        'worker_num'         => function_exists('\swoole_cpu_num') ? \swoole_cpu_num() * 2 : 8,
        //'task_worker_num'   => function_exists('\swoole_cpu_num') ? \swoole_cpu_num() * 2 : 8,
        'task_ipc_mode'      => 3,
        'task_max_request'   => 3000,
        'task_tmpdir'        => is_dir('/dev/shm') && is_writable('/dev/shm') ? '/dev/shm' : '/tmp',
        'message_queue_key'  => sprintf('sw_%s', md5(base_path())),
        'max_request'        => 3000,
        'open_tcp_nodelay'   => true,
        'pid_file'           => storage_path('laravels.pid'),
        'log_file'           => storage_path(sprintf('logs/swoole-%s.log', date('Y-m'))),
        'log_level'          => 4,
        'document_root'      => base_path('public'),
        'buffer_output_size' => 16 * 1024 * 1024,
        'socket_buffer_size' => 128 * 1024 * 1024,
        'reload_async'       => true,
        'max_wait_time'      => 60,
        'enable_reuse_port'  => true,

        /**
         * More settings of Swoole
         * @see https://wiki.swoole.com/wiki/page/274.html  Chinese
         * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration  English
         */
    ],
    'events'         => [
    ],
];
