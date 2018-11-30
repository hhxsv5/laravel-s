<?php

namespace Hhxsv5\LaravelS\Swoole;

use Hhxsv5\LaravelS\Swoole\Socket\PortInterface;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Traits\LogTrait;
use Hhxsv5\LaravelS\Swoole\Traits\ProcessTitleTrait;

class Server
{
    use LogTrait;
    use ProcessTitleTrait;

    protected $conf;

    /**
     * @var \swoole_http_server|\swoole_websocket_server
     */
    protected $swoole;

    protected $enableWebSocket = false;

    protected $attachedSockets = [];

    protected function __construct(array $conf)
    {
        $this->conf = $conf;
        $this->enableWebSocket = !empty($this->conf['websocket']['enable']);
        $this->attachedSockets = empty($this->conf['sockets']) ? [] : $this->conf['sockets'];

        $ip = isset($conf['listen_ip']) ? $conf['listen_ip'] : '127.0.0.1';
        $port = isset($conf['listen_port']) ? $conf['listen_port'] : 5200;
        $socketType = isset($conf['socket_type']) ? (int)$conf['socket_type'] : \SWOOLE_SOCK_TCP;

        if ($socketType === \SWOOLE_SOCK_UNIX_STREAM) {
            $socketDir = dirname($ip);
            if (!file_exists($socketDir)) {
                mkdir($socketDir);
            }
        }

        $settings = isset($conf['swoole']) ? $conf['swoole'] : [];
        $settings['enable_static_handler'] = !empty($conf['handle_static']);

        $serverClass = $this->enableWebSocket ? \swoole_websocket_server::class : \swoole_http_server::class;
        if (isset($settings['ssl_cert_file'], $settings['ssl_key_file'])) {
            $this->swoole = new $serverClass($ip, $port, \SWOOLE_PROCESS, $socketType | \SWOOLE_SSL);
        } else {
            $this->swoole = new $serverClass($ip, $port, \SWOOLE_PROCESS, $socketType);
        }

        $this->swoole->set($settings);

        $this->bindBaseEvent();
        $this->bindHttpEvent();
        $this->bindTaskEvent();
        $this->bindWebSocketEvent();
        $this->bindAttachedSockets();
        $this->bindSwooleTables();
    }

    protected function bindBaseEvent()
    {
        $this->swoole->on('Start', [$this, 'onStart']);
        $this->swoole->on('Shutdown', [$this, 'onShutdown']);
        $this->swoole->on('ManagerStart', [$this, 'onManagerStart']);
        $this->swoole->on('ManagerStop', [$this, 'onManagerStop']);
        $this->swoole->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->swoole->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->swoole->on('WorkerError', [$this, 'onWorkerError']);
        $this->swoole->on('PipeMessage', [$this, 'onPipeMessage']);
    }

    protected function bindHttpEvent()
    {
        $this->swoole->on('Request', [$this, 'onRequest']);
    }

    protected function bindTaskEvent()
    {
        if (!empty($this->conf['swoole']['task_worker_num'])) {
            $this->swoole->on('Task', [$this, 'onTask']);
            $this->swoole->on('Finish', [$this, 'onFinish']);
        }
    }

    protected function bindWebSocketEvent()
    {
        if ($this->enableWebSocket) {
            $eventHandler = function ($method, array $params) {
                $this->callWithCatchException(function () use ($method, $params) {
                    call_user_func_array([$this->getWebSocketHandler(), $method], $params);
                });
            };

            $this->swoole->on('Open', function () use ($eventHandler) {
                $eventHandler('onOpen', func_get_args());
            });

            $this->swoole->on('Message', function () use ($eventHandler) {
                $eventHandler('onMessage', func_get_args());
            });

            $this->swoole->on('Close', function (\swoole_websocket_server $server, $fd, $reactorId) use ($eventHandler) {
                $clientInfo = $server->getClientInfo($fd);
                if (isset($clientInfo['websocket_status']) && $clientInfo['websocket_status'] === \WEBSOCKET_STATUS_FRAME) {
                    $eventHandler('onClose', func_get_args());
                }
                // else ignore the close event for http server
            });
        }
    }

    protected function bindAttachedSockets()
    {
        foreach ($this->attachedSockets as $socket) {
            $port = $this->swoole->addListener($socket['host'], $socket['port'], $socket['type']);
            if (!($port instanceof \swoole_server_port)) {
                $errno = method_exists($this->swoole, 'getLastError') ? $this->swoole->getLastError() : 'unknown';
                $errstr = sprintf('listen %s:%s failed: errno=%s', $socket['host'], $socket['port'], $errno);
                $this->log($errstr, 'ERROR');
                continue;
            }

            $port->set(empty($socket['settings']) ? [] : $socket['settings']);

            $handlerClass = $socket['handler'];
            $eventHandler = function ($method, array $params) use ($port, $handlerClass) {
                $handler = $this->getSocketHandler($port, $handlerClass);
                if (method_exists($handler, $method)) {
                    $this->callWithCatchException(function () use ($handler, $method, $params) {
                        call_user_func_array([$handler, $method], $params);
                    });
                }
            };
            static $events = [
                'Open',
                'Request',
                'Message',
                'Connect',
                'Close',
                'Receive',
                'Packet',
                'BufferFull',
                'BufferEmpty',
            ];
            foreach ($events as $event) {
                $port->on($event, function () use ($event, $eventHandler) {
                    $eventHandler('on' . $event, func_get_args());
                });
            }
        }
    }

    protected function getWebSocketHandler()
    {
        static $handler = null;
        if ($handler !== null) {
            return $handler;
        }

        $handlerClass = $this->conf['websocket']['handler'];
        $t = new $handlerClass();
        if (!($t instanceof WebSocketHandlerInterface)) {
            throw new \Exception(sprintf('%s must implement the interface %s', get_class($t), WebSocketHandlerInterface::class));
        }
        $handler = $t;
        return $handler;
    }

    protected function getSocketHandler(\swoole_server_port $port, $handlerClass)
    {
        static $handlers = [];
        $portHash = spl_object_hash($port);
        if (isset($handlers[$portHash])) {
            return $handlers[$portHash];
        }
        $t = new $handlerClass($port);
        if (!($t instanceof PortInterface)) {
            throw new \Exception(sprintf('%s must extend the abstract class TcpSocket/UdpSocket', get_class($t)));
        }
        $handlers[$portHash] = $t;
        return $handlers[$portHash];
    }

    protected function bindSwooleTables()
    {
        $tables = isset($this->conf['swoole_tables']) ? (array)$this->conf['swoole_tables'] : [];
        foreach ($tables as $name => $table) {
            $t = new \swoole_table($table['size']);
            foreach ($table['column'] as $column) {
                if (isset($column['size'])) {
                    $t->column($column['name'], $column['type'], $column['size']);
                } else {
                    $t->column($column['name'], $column['type']);
                }
            }
            $t->create();
            $name .= 'Table'; // Avoid naming conflicts
            $this->swoole->$name = $t;
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
            if (!empty($this->conf['enable_coroutine_runtime'])) {
                \Swoole\Runtime::enableCoroutine();
            }
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

    public function onWorkerError(\swoole_http_server $server, $workerId, $workerPId, $exitCode, $signal)
    {
        $this->log(sprintf('worker[%d] error: exitCode=%s, signal=%s', $workerId, $exitCode, $signal), 'ERROR');
    }

    public function onPipeMessage(\swoole_http_server $server, $srcWorkerId, $message)
    {
        if ($message instanceof Task) {
            $this->onTask($server, uniqid('', true), $srcWorkerId, $message);
        }
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {

    }

    public function onTask(\swoole_http_server $server, $taskId, $srcWorkerId, $data)
    {
        if ($data instanceof Event) {
            $this->handleEvent($data);
        } elseif ($data instanceof Task) {
            if ($this->handleTask($data) && method_exists($data, 'finish')) {
                return $data;
            }
        }
    }

    public function onFinish(\swoole_http_server $server, $taskId, $data)
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
        if (!is_array($listenerClasses)) {
            $listenerClasses = (array)$listenerClasses;
        }
        foreach ($listenerClasses as $listenerClass) {
            /**
             * @var Listener $listener
             */
            $listener = new $listenerClass();
            if (!($listener instanceof Listener)) {
                throw new \Exception(sprintf('%s must extend the abstract class %s', $listenerClass, Listener::class));
            }
            $this->callWithCatchException(function () use ($listener, $event) {
                $listener->handle($event);
            });
        }
    }

    protected function handleTask(Task $task)
    {
        return $this->callWithCatchException(function () use ($task) {
            $task->handle();
            return true;
        });
    }

    public function run()
    {
        $this->swoole->start();
    }
}
