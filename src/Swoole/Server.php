<?php

namespace Hhxsv5\LaravelS\Swoole;

use Hhxsv5\LaravelS\Swoole\Websocket\Handler as WebsocketHandler;

class Server
{
    protected $conf;

    /**
     * @var \swoole_http_server
     */
    protected $swoole;

    protected static $swooleDefaultSettings = [
        'reload_async'      => true,
        'max_wait_time'     => 60,
        'enable_reuse_port' => true,
    ];

    protected $enableWebsocket = false;

    protected function __construct(array $conf)
    {
        $this->conf = $conf;
        $this->enableWebsocket = !empty($this->conf['websocket']['enable']);

        $ip = isset($conf['listen_ip']) ? $conf['listen_ip'] : '0.0.0.0';
        $port = isset($conf['listen_port']) ? $conf['listen_port'] : 8841;
        $settings = isset($conf['swoole']) ? $conf['swoole'] : [];
        $settings['enable_static_handler'] = !empty($conf['handle_static']);

        $serverClass = $this->enableWebsocket ? \swoole_websocket_server::class : \swoole_http_server::class;
        if (isset($settings['ssl_cert_file'], $settings['ssl_key_file'])) {
            $this->swoole = new $serverClass($ip, $port, \SWOOLE_PROCESS, \SWOOLE_SOCK_TCP | \SWOOLE_SSL);
        } else {
            $this->swoole = new $serverClass($ip, $port, \SWOOLE_PROCESS);
        }

        $this->swoole->set($settings + self::$swooleDefaultSettings);
        $this->bindEvent();
    }

    protected function bindEvent()
    {
        $this->swoole->on('Start', [$this, 'onStart']);
        $this->swoole->on('Shutdown', [$this, 'onShutdown']);
        $this->swoole->on('ManagerStart', [$this, 'onManagerStart']);
        $this->swoole->on('ManagerStop', [$this, 'onManagerStop']);
        $this->swoole->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->swoole->on('WorkerStop', [$this, 'onWorkerStop']);
        if (version_compare(\swoole_version(), '1.9.17', '>=')) {
            $this->swoole->on('WorkerExit', [$this, 'onWorkerExit']);
        }
        $this->swoole->on('WorkerError', [$this, 'onWorkerError']);
        $this->swoole->on('Request', [$this, 'onRequest']);

        if (!empty($this->conf['swoole']['task_worker_num'])) {
            $this->swoole->on('Task', [$this, 'onTask']);
            $this->swoole->on('Finish', [$this, 'onFinish']);
        }

        $this->bindWebsocketEvent();
    }

    protected function bindWebsocketEvent()
    {
        if ($this->enableWebsocket) {
            $handler = new ($this->conf['websocket']['handler']);
            if (!($handler instanceof WebsocketHandler)) {
                throw new \Exception(sprintf('%s must implement the interface %s', get_class($handler), WebsocketHandler::class);
            }
            $this->swoole->on('Open', [$handler, 'onOpen']);
            $this->swoole->on('Message', [$handler, 'onMessage']);
            $this->swoole->on('Close', [$handler, 'onClose']);
        }
    }

    public function onStart(\swoole_http_server $server)
    {
        foreach (spl_autoload_functions() as $function) {
            spl_autoload_unregister($function);
        }

        $this->setProcessTitle(sprintf('%s laravels: master process', $this->conf['process_prefix']));

        if (version_compare(\swoole_version(), '1.9.5', '<')) {
            file_put_contents($this->conf['swoole']['pid_file'], $server->master_pid);
        }
    }

    public function onShutdown(\swoole_http_server $server)
    {

    }

    public function onManagerStart(\swoole_http_server $server)
    {
        $this->setProcessTitle(sprintf('%s laravels: manager process', $this->conf['process_prefix']));
    }

    public function onManagerStop(\swoole_http_server $server)
    {

    }

    public function onWorkerStart(\swoole_http_server $server, $workerId)
    {
        if ($workerId >= $server->setting['worker_num']) {
            $process = 'task worker';
        } else {
            $process = 'worker';
        }
        $this->setProcessTitle(sprintf('%s laravels: %s process %d', $this->conf['process_prefix'], $process, $workerId));

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        clearstatcache();
    }

    public function onWorkerStop(\swoole_http_server $server, $workerId)
    {

    }

    public function onWorkerExit(\swoole_http_server $server, $workerId)
    {

    }

    public function onWorkerError(\swoole_http_server $server, $workerId, $workerPId, $exitCode, $signal)
    {

    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {

    }

    public function onTask(\swoole_http_server $server, $taskId, $srcWorkerId, $data)
    {

    }

    public function onFinish(\swoole_http_request $server, $taskId, $data)
    {

    }

    public function run()
    {
        $this->swoole->start();
    }

    protected function setProcessTitle($title)
    {
        if (PHP_OS === 'Darwin') {
            return;
        }
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($title);
        } elseif (function_exists('\swoole_set_process_name')) {
            \swoole_set_process_name($title);
        }
    }

}