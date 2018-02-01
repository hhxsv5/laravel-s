<?php

namespace Hhxsv5\LaravelS\Swoole;

class Server
{
    protected $conf;
    protected $swoole;

    protected function __construct(array $conf = [])
    {
        $this->conf = $conf;

        $ip = isset($conf['listen_ip']) ? $conf['listen_ip'] : '0.0.0.0';
        $port = isset($conf['listen_port']) ? $conf['listen_port'] : 8841;
        $settings = isset($conf['swoole']) ? $conf['swoole'] : [];

        if (isset($settings['ssl_cert_file'], $settings['ssl_key_file'])) {
            $this->swoole = new \swoole_http_server($ip, $port, \SWOOLE_PROCESS, \SWOOLE_SOCK_TCP | \SWOOLE_SSL);
        } else {
            $this->swoole = new \swoole_http_server($ip, $port, \SWOOLE_PROCESS);
        }

        $default = [
            'reload_async'      => true,
            'max_wait_time'     => 60,
            'enable_reuse_port' => true,
        ];

        $this->swoole->set($settings + $default);
    }

    protected function bind()
    {
        $this->swoole->on('Start', [$this, 'onStart']);
        $this->swoole->on('Shutdown', [$this, 'onShutdown']);
        $this->swoole->on('ManagerStart', [$this, 'onManagerStart']);
        $this->swoole->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->swoole->on('WorkerStop', [$this, 'onWorkerStop']);
        if (version_compare(\swoole_version(), '1.9.17', '>=')) {
            $this->swoole->on('WorkerExit', [$this, 'onWorkerExit']);
        }
        $this->swoole->on('WorkerError', [$this, 'onWorkerError']);
        $this->swoole->on('Request', [$this, 'onRequest']);
    }

    public function onStart(\swoole_http_server $server)
    {
        $this->setProcessTitle('laravels: master process');
    }

    public function onShutdown(\swoole_http_server $server)
    {

    }

    public function onManagerStart(\swoole_http_server $server)
    {
        $this->setProcessTitle('laravels: manager process');
    }

    public function onWorkerStart(\swoole_http_server $server, $workerId)
    {
        $this->setProcessTitle('laravels: worker process ' . $workerId);

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

    public function run()
    {
        $this->bind();
        $this->swoole->start();
    }

    protected function setProcessTitle($title)
    {
        if (PHP_OS == 'Darwin') {
            return;
        }
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($title);
        } elseif (function_exists('\swoole_set_process_name')) {
            \swoole_set_process_name($title);
        }
    }

}