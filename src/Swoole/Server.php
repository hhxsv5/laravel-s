<?php

namespace Hhxsv5\LaravelS\Swoole;

use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
use Hhxsv5\LaravelS\Swoole\Task\Task;

class Server
{
    protected $conf;

    /**
     * @var \swoole_http_server
     */
    protected $swoole;

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

        $this->swoole->set($settings);

        $this->bindHttpEvent();
        $this->bindTaskEvent();
        $this->bindWebsocketEvent();
    }

    protected function bindHttpEvent()
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
    }

    protected function bindTaskEvent()
    {
        if (!empty($this->conf['swoole']['task_worker_num'])) {
            $this->swoole->on('Task', [$this, 'onTask']);
            $this->swoole->on('Finish', [$this, 'onFinish']);
        }
    }

    protected function bindWebsocketEvent()
    {
        if ($this->enableWebsocket) {
            $this->swoole->on('Open', function () {
                $handler = $this->getWebsocketHandler();
                try {
                    call_user_func_array([$handler, 'onOpen'], func_get_args());
                } catch (\Exception $e) {
                    // Do nothing to avoid 'zend_mm_heap corrupted'
                }
            });

            $this->swoole->on('Message', function () {
                $handler = $this->getWebsocketHandler();
                try {
                    call_user_func_array([$handler, 'onMessage'], func_get_args());
                } catch (\Exception $e) {
                    // Do nothing to avoid 'zend_mm_heap corrupted'
                }
            });

            $this->swoole->on('Close', function () {
                $handler = $this->getWebsocketHandler();
                try {
                    call_user_func_array([$handler, 'onClose'], func_get_args());
                } catch (\Exception $e) {
                    // Do nothing to avoid 'zend_mm_heap corrupted'
                }
            });
        }
    }

    protected function getWebsocketHandler()
    {
        static $handler = null;
        if ($handler !== null) {
            return $handler;
        }

        $handlerClass = $this->conf['websocket']['handler'];
        $t = new $handlerClass();
        if (!($t instanceof WebsocketHandlerInterface)) {
            throw new \Exception(sprintf('%s must implement the interface %s', get_class($handler), WebsocketHandlerInterface::class));
        }
        $handler = $t;
        return $handler;
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
        if ($data instanceof Event) {
            $this->handleEvent($data);
        } elseif ($data instanceof Task) {
            $this->handleTask($data);
            if (method_exists($data, 'finish')) {
                return $data;
            }
        }
    }

    public function onFinish(\swoole_http_request $server, $taskId, $data)
    {
        if ($data instanceof Task) {
            $data->finish();
        }
    }

    protected function handleEvent(Event $event)
    {
        $eventClass = get_class($event);
        if (!isset($this->conf['events'][$eventClass])) {
            return;
        }

        $listenerClasses = $this->conf['events'][$eventClass];
        try {
            if (!is_array($listenerClasses)) {
                $listenerClasses = (array)$listenerClasses;
            }
            foreach ($listenerClasses as $listenerClass) {
                /**
                 * @var Listener $listener
                 */
                $listener = new $listenerClass();
                $listener->handle($event);
            }
        } catch (\Exception $e) {
            // Do nothing to avoid 'zend_mm_heap corrupted'
        }
    }

    protected function handleTask(Task $task)
    {
        try {
            $task->handle();
        } catch (\Exception $e) {
            // Do nothing to avoid 'zend_mm_heap corrupted'
        }
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