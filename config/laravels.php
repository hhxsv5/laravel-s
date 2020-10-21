<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Listen IP
    |--------------------------------------------------------------------------
    |
    | This value represents the IP on which the Swoole server will run.
    | 
    | To bind all IPs on the server use 0.0.0.0 or your server IP
    | If you only want to access locally, use 127.0.0.1
    |
    */

    'listen_ip' => env('LARAVELS_LISTEN_IP', '127.0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Listen Port
    |--------------------------------------------------------------------------
    |
    | This value represents the port on which the server will run.
    | 
    | If you are using a webserver to reverse proxy, we recommend that you use 8080
    |
    */

    'listen_port' => env('LARAVELS_LISTEN_PORT', 5200),

    /*
    |--------------------------------------------------------------------------
    | Socket Type
    |--------------------------------------------------------------------------
    |
    | Usually, you don’t need to care about it. 
    | Unless you want Nginx to proxy to the UnixSocket Stream file, you need 
    | to modify it to SWOOLE_SOCK_UNIX_STREAM, and listen_ip is the path of UnixSocket Stream file.
    |
    | Read more at this link:
    | https://www.swoole.co.uk/docs/modules/swoole-server-doc
    |
    */

    'socket_type' => defined('SWOOLE_SOCK_TCP') ? SWOOLE_SOCK_TCP : 1,

    /*
    |--------------------------------------------------------------------------
    | Coroutine Runtime
    |--------------------------------------------------------------------------
    |
    | This value represents whether the coroutine runtime is activated or not.
    | 
    | Read more at this link:
    | https://www.swoole.co.uk/docs/modules/swoole-runtime-flags
    |
    */

    'enable_coroutine_runtime' => false,

    /*
    |--------------------------------------------------------------------------
    | Server Name
    |--------------------------------------------------------------------------
    |
    | This value represents the name of the server that will be 
    | displayed in the header of each request.
    |
    */

    'server' => env('LARAVELS_SERVER', 'LaravelS'),

    /*
    |--------------------------------------------------------------------------
    | Handle Static
    |--------------------------------------------------------------------------
    |
    | Whether handle the static resource by LaravelS.
    | Suggest that Nginx handles the statics and LaravelS handles the dynamics. 
    | The default path of static resource is base_path('public'), 
    | you can modify swoole.document_root to change it.
    |
    */

    'handle_static' => env('LARAVELS_HANDLE_STATIC', false),

    /*
    |--------------------------------------------------------------------------
    | Base Path
    |--------------------------------------------------------------------------
    |
    | The basic path of Laravel, default base_path(), be used for symbolic link.
    |
    */

    'laravel_base_path' => env('LARAVEL_BASE_PATH', base_path()),

    /*
    |--------------------------------------------------------------------------
    | Inotify
    |--------------------------------------------------------------------------
    |
    | For this, it is necessary to have this extension installed:
    | https://pecl.php.net/package/inotify
    |
    */

    'inotify_reload' => [
        // Whether enable the Inotify Reload to reload all worker processes when your code is modified,
        // depend on inotify
        'enable'        => env('LARAVELS_INOTIFY_RELOAD', false),

        // The file path that Inotify watches
        'watch_path'    => base_path(),

        // The file types that Inotify watches
        'file_types'    => ['.php'],

        // The excluded/ignored directories that Inotify watches
        'excluded_dirs' => [],

        // Whether output the reload log
        'log'           => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Handlers
    |--------------------------------------------------------------------------
    |
    | Configure the event callback function of Swoole, key-value format, 
    | key is the event name, and value is the class that implements the event 
    | processing interface.
    |
    */

    'event_handlers' => [],

    /*
    |--------------------------------------------------------------------------
    | WebSockets
    |--------------------------------------------------------------------------
    |
    | Swoole WebSocket server settings.
    |
    | Read more at this link:
    | https://www.swoole.co.uk/docs/modules/swoole-websocket-server
    |
    */

    'websocket' => [
        'enable' => false,
        // 'handler' => XxxWebSocketHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sockets
    |--------------------------------------------------------------------------
    |
    | The socket list for TCP/UDP.
    |
    | Read more at this link:
    | https://www.swoole.co.uk/docs/modules/swoole-client
    |
    */

    'sockets' => [],

    /*
    |--------------------------------------------------------------------------
    | Processes
    |--------------------------------------------------------------------------
    |
    | Support developers to create special work processes for monitoring, 
    | reporting, or other special tasks.
    |
    | Read more at this link:
    | https://github.com/hhxsv5/laravel-s/blob/master/README.md#custom-process
    |
    */

    'processes' => [],

    /*
    |--------------------------------------------------------------------------
    | Timer
    |--------------------------------------------------------------------------
    |
    | Wrapper cron job base on Swoole's Millisecond Timer, replace Linux Crontab.
    |
    | Read more at this link:
    | https://github.com/hhxsv5/laravel-s/blob/master/README.md#millisecond-cron-job
    |
    */

    'timer' => [
        'enable'          => env('LARAVELS_TIMER', false),

        // The list of cron job
        'jobs'            => [],

        // Max waiting time of reloading
        'max_wait_time'   => 5,

        // Enable the global lock to ensure that only one instance starts the timer 
        // when deploying multiple instances. 
        // This feature depends on Redis https://laravel.com/docs/8.x/redis
        'global_lock'     => false,
        'global_lock_key' => config('app.name', 'Laravel'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | All defined tables will be created before Swoole starting.
    |
    | Read more at this link:
    | https://github.com/hhxsv5/laravel-s/blob/master/README.md#use-swooletable
    |
    */

    'swoole_tables' => [],

    /*
    |--------------------------------------------------------------------------
    | Register Providers
    |--------------------------------------------------------------------------
    |
    | The Service Provider list, will be re-registered each request, and run method boot() 
    | if it exists. Usually, be used to clear the Service Provider 
    | which registers Singleton instances.
    |
    | Read more at this link:
    | https://github.com/hhxsv5/laravel-s/blob/master/Settings.md#register_providers
    |
    */

    'register_providers' => [],

    /*
    |--------------------------------------------------------------------------
    | Cleaners
    |--------------------------------------------------------------------------
    |
    | The list of cleaners for each request is used to clean up some residual 
    | global variables, singleton objects, and static properties to avoid 
    | data pollution between requests.
    |
    | Read more at this link:
    | https://github.com/hhxsv5/laravel-s/blob/master/Settings.md#cleaners
    |
    */

    'cleaners' => [],

    /*
    |--------------------------------------------------------------------------
    | Destroy Controllers
    |--------------------------------------------------------------------------
    |
    | Automatically destroy the controllers after each request to solve 
    | the problem of the singleton controllers.
    |
    | Read more at this link:
    | https://github.com/hhxsv5/laravel-s/blob/master/Settings.md#destroy_controllers
    |
    */

    'destroy_controllers' => [
        'enable'        => false,
        'excluded_list' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Swoole
    |--------------------------------------------------------------------------
    |
    | Swoole's original configuration items.
    |
    | Read more at this link:
    | https://www.swoole.co.uk/docs/modules/swoole-server/configuration
    |
    */

    'swoole' => [
        /* 
        |
        | Daemonize the swoole server process.
        | The program which wants to run a long time must enable this configuration.
        |
        */
        'daemonize'          => env('LARAVELS_DAEMONIZE', false),

        /* 
        |
        | The mode of dispatching connections to the worker process.
        | This parameter only works for the SWOOLE_PROCESS mode swoole_server.
        |
        */
        'dispatch_mode'      => 2,

        /* 
        |
        | The number of the reactor threads.
        | To utilise all the CPUs, the default number of reactor_num is the number of core in CPU.
        | **reactor_num has to be smaller than the worker_num.**
        |
        */
        'reactor_num'        => env('LARAVELS_REACTOR_NUM', function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 4),

        /* 
        |
        | The number of worker process.
        | If the code of logic is asynchronous and non-blocking, 
        | set the worker_num to the value from one time to four times of CPU cores.
        |
        */
        'worker_num'         => env('LARAVELS_WORKER_NUM', function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 8),

        /* 
        |
        | Set the communication mode between the task worker process and worker process.
        | The message queue uses the memory queue provided by os to store the data.
        |
        */
        'task_ipc_mode'      => 1,

        /* 
        |
        | After handling the number of task_max_request tasks, the task worker process will exit 
        | and release all the memory and resource used by this process. 
        | And then, the manager will respawn a new task worker process.
        |
        */
        'task_max_request'   => env('LARAVELS_TASK_MAX_REQUEST', 8000),

        /* 
        |
        | Set the path of temporary task data.
        | If size of task message exceeds 8192 bytes, swoole uses the temporary file to store the data.
        |
        */
        'task_tmpdir'        => @is_writable('/dev/shm/') ? '/dev/shm' : '/tmp',

        /* 
        |
        | A worker process is restarted to avoid memory leak when receving 
        | max_request + rand(0, max_request_grace) requests.
        |
        */
        'max_request'        => env('LARAVELS_MAX_REQUEST', 8000),

        /* 
        |
        | Open this configuration to close the Nagle algorithm.
        |
        */
        'open_tcp_nodelay'   => true,

        /* 
        |
        | The file path which the master process id saves in.
        |
        */
        'pid_file'           => storage_path('laravels.pid'),

        /* 
        |
        | Set the log path of Swoole.
        |
        */
        'log_file'           => storage_path(sprintf('logs/swoole-%s.log', date('Y-m'))),

        /* 
        |
        | Set the level of the log.
        | The log that is inferior to the log_level set will not be recorded to log file.
        |
        */
        'log_level'          => 4,

        /* 
        |
        | The basic path of Laravel, default base_path(), be used for symbolic link.
        |
        */
        'document_root'      => base_path('public'),

        /* 
        |
        | Set the output buffer size in the memory.
        | The default value is 2M. The data to send can't be larger than buffer_output_size every time.
        |
        */
        'buffer_output_size' => 2 * 1024 * 1024,

        /* 
        |
        | Set the buffer size of the socket.
        | This configuration is to set the max memory size of the connection.
        |
        */
        'socket_buffer_size' => 128 * 1024 * 1024,

        /* 
        |
        | The max length of a package whose unit is byte.
        | Once the configuration open_length_check/open_eof_check/open_http_protocol has enabled, 
        | the internal of swoole will joint the data received from the client and the data stores in 
        | the memory before receiving the whole package. 
        |
        | So to limit the usage of memory, it should set the package_max_length.
        |
        */
        'package_max_length' => 4 * 1024 * 1024,

        /* 
        |
        | By enabling reload_async, the worker processes shutdown after processing all the pending events.
        |
        */
        'reload_async'       => true,

        /* 
        |
        | The max waiting time to restart a worker process.
        |
        */
        'max_wait_time'      => 60,

        /* 
        |
        | Enable the reuse of port.
        |
        */
        'enable_reuse_port'  => true,

        /* 
        |
        | Enable coroutine support in task worker.
        |
        */
        'enable_coroutine'   => false,

        /* 
        |
        | Enable or disable compression for HTTP response.
        | There are three types of compression: gzip, br, deflate supported 
        | and used based on the Accept-Encoding HTTP header from HTTP request.
        |
        */
        'http_compression'   => false,
        // 'http_compression_level' => 1


        /*
        |
        | Request slow log
        |
        */
        // 'request_slowlog_timeout' => 2,
        // 'request_slowlog_file'    => storage_path(sprintf('logs/slow-%s.log', date('Y-m'))),
        // 'trace_event_worker'      => true,

        /**
         * More settings of Swoole
         * @see https://wiki.swoole.com/#/server/setting  Chinese
         * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration  English
         */
    ],
];
