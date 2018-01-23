<?php

namespace Hhxsv5\LaravelS\Swoole;

class Server
{
    protected $svrConf;
    protected $sw;

    protected function __construct(array $svrConf = [])
    {
        $this->svrConf = $svrConf;

        $ip = isset($svrConf['listen_ip']) ? $svrConf['listen_ip'] : '0.0.0.0';
        $port = isset($svrConf['listen_port']) ? $svrConf['listen_port'] : 8841;
        $settings = isset($svrConf['swoole']) ? $svrConf['swoole'] : [];

        if (isset($settings['ssl_cert_file'], $settings['ssl_key_file'])) {
            $this->sw = new \swoole_http_server($ip, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
        } else {
            $this->sw = new \swoole_http_server($ip, $port, SWOOLE_PROCESS);
        }

        $this->sw->set($settings);
    }

    protected function bind()
    {
        $this->sw->on('Start', [$this, 'onStart']);
        $this->sw->on('Shutdown', [$this, 'onShutdown']);
        $this->sw->on('ManagerStart', [$this, 'onManagerStart']);
        $this->sw->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->sw->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->sw->on('WorkerExit', [$this, 'onWorkerExit']);
        $this->sw->on('WorkerError', [$this, 'onWorkerError']);
        $this->sw->on('Request', [$this, 'onRequest']);
    }

    public function onStart(\swoole_http_server $server)
    {
        global $argv;
        $title = sprintf('php %s master process', implode(' ', $argv));
        $this->setProcessTitle($title);

        file_put_contents($this->svrConf['pid_file'], $server->master_pid);
    }

    public function onShutdown(\swoole_http_server $server)
    {
        @unlink($this->svrConf['pid_file']);
    }

    public function onManagerStart(\swoole_http_server $server)
    {
        global $argv;
        $title = sprintf('php %s manager process', implode(' ', $argv));
        $this->setProcessTitle($title);
    }

    public function onWorkerStart(\swoole_http_server $server, $workerId)
    {
        \Log::info('Laravels:onWorkerStart: already included files(cannot work by reload)', get_included_files());

        global $argv;
        $title = sprintf('php %s worker process %d', implode(' ', $argv), $workerId);
        $this->setProcessTitle($title);
    }

    public function onWorkerStop(\swoole_http_server $server, $workerId)
    {

    }

    public function onWorkerExit(\swoole_http_server $server, $workerId)
    {

    }

    public function onWorkerError(\swoole_http_server $server, $workerId, $workerPid, $exitCode, $signal)
    {

    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {

    }

    public function run()
    {
        $this->bind();
        $this->sw->start();
    }

    protected function setProcessTitle($title)
    {
        if (PHP_OS == 'Darwin') {
            return;
        }
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($title);
        } elseif (function_exists('swoole_set_process_name')) {
            swoole_set_process_name($title);
        }
    }

}