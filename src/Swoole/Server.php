<?php

namespace Hhxsv5\LaravelS\Swoole;

use Hhxsv5\LaravelS\Illuminate\LogTrait;
use Hhxsv5\LaravelS\Swoole\Process\ProcessTitleTrait;
use Hhxsv5\LaravelS\Swoole\Socket\PortInterface;
use Hhxsv5\LaravelS\Swoole\Task\BaseTask;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server as HttpServer;
use Swoole\Server\Port;
use Swoole\Table;
use Swoole\WebSocket\Server as WebSocketServer;

class Server
{
    use LogTrait;
    use ProcessTitleTrait;

    /**@var array */
    protected $conf;

    /**@var HttpServer|WebSocketServer */
    protected $swoole;

    /**@var bool */
    protected $enableWebSocket = false;

    protected function __construct(array $conf)
    {
        $this->conf = $conf;
        $this->enableWebSocket = !empty($this->conf['websocket']['enable']);

        $ip = isset($conf['listen_ip']) ? $conf['listen_ip'] : '127.0.0.1';
        $port = isset($conf['listen_port']) ? $conf['listen_port'] : 5200;
        $socketType = isset($conf['socket_type']) ? (int)$conf['socket_type'] : SWOOLE_SOCK_TCP;

        if ($socketType === SWOOLE_SOCK_UNIX_STREAM) {
            $socketDir = dirname($ip);
            if (!file_exists($socketDir) && !mkdir($socketDir) && !is_dir($socketDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $socketDir));
            }
        }

        $settings = isset($conf['swoole']) ? $conf['swoole'] : [];
        $settings['enable_static_handler'] = !empty($conf['handle_static']);

        $serverClass = $this->enableWebSocket ? WebSocketServer::class : HttpServer::class;
        if (isset($settings['ssl_cert_file'], $settings['ssl_key_file'])) {
            $this->swoole = new $serverClass($ip, $port, SWOOLE_PROCESS, $socketType | SWOOLE_SSL);
        } else {
            $this->swoole = new $serverClass($ip, $port, SWOOLE_PROCESS, $socketType);
        }

        // Disable Coroutine
        $settings['enable_coroutine'] = false;

        $this->swoole->set($settings);

        $this->bindBaseEvents();
        $this->bindHttpEvents();
        $this->bindTaskEvents();
        $this->bindWebSocketEvents();
        $this->bindPortEvents();
        $this->bindSwooleTables();

        // Disable Hook
        class_exists('Swoole\Coroutine') && \Swoole\Coroutine::set(['hook_flags' => false]);
    }

    protected function bindBaseEvents()
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

    protected function bindHttpEvents()
    {
        $this->swoole->on('Request', [$this, 'onRequest']);
    }

    protected function bindTaskEvents()
    {
        if (!empty($this->conf['swoole']['task_worker_num'])) {
            $this->swoole->on('Task', [$this, 'onTask']);
            $this->swoole->on('Finish', [$this, 'onFinish']);
        }
    }

    protected function triggerWebSocketEvent($event, array $params)
    {
        return $this->callWithCatchException(function () use ($event, $params) {
            $handler = $this->getWebSocketHandler();

            if (method_exists($handler, $event)) {
                call_user_func_array([$handler, $event], $params);
            } elseif ($event === 'onHandShake') {
                // Set default HandShake
                call_user_func_array([$this, 'onHandShake'], $params);
            }
        });
    }

    protected function bindWebSocketEvents()
    {
        if ($this->enableWebSocket) {
            $this->swoole->on('HandShake', function () {
                return $this->triggerWebSocketEvent('onHandShake', func_get_args());
            });

            $this->swoole->on('Open', function () {
                $this->triggerWebSocketEvent('onOpen', func_get_args());
            });

            $this->swoole->on('Message', function () {
                $this->triggerWebSocketEvent('onMessage', func_get_args());
            });

            $this->swoole->on('Close', function (WebSocketServer $server, $fd, $reactorId) {
                $clientInfo = $server->getClientInfo($fd);
                if (isset($clientInfo['websocket_status']) && $clientInfo['websocket_status'] === \WEBSOCKET_STATUS_FRAME) {
                    $this->triggerWebSocketEvent('onClose', func_get_args());
                }
                // else ignore the close event for http server
            });
        }
    }

    protected function triggerPortEvent(Port $port, $handlerClass, $event, array $params)
    {
        return $this->callWithCatchException(function () use ($port, $handlerClass, $event, $params) {
            $handler = $this->getSocketHandler($port, $handlerClass);

            if (method_exists($handler, $event)) {
                call_user_func_array([$handler, $event], $params);
            } elseif ($event === 'onHandShake') {
                // Set default HandShake
                call_user_func_array([$this, 'onHandShake'], $params);
            }
        });
    }

    protected function bindPortEvents()
    {
        $sockets = empty($this->conf['sockets']) ? [] : $this->conf['sockets'];
        foreach ($sockets as $socket) {
            if (isset($socket['enable']) && !$socket['enable']) {
                continue;
            }

            $port = $this->swoole->addListener($socket['host'], $socket['port'], $socket['type']);
            if (!($port instanceof Port)) {
                $errno = method_exists($this->swoole, 'getLastError') ? $this->swoole->getLastError() : 'unknown';
                $errstr = sprintf('listen %s:%s failed: errno=%s', $socket['host'], $socket['port'], $errno);
                $this->error($errstr);
                continue;
            }

            $port->set(empty($socket['settings']) ? [] : $socket['settings']);

            $handlerClass = $socket['handler'];

            $events = [
                'Open',
                'HandShake',
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
                $port->on($event, function () use ($port, $handlerClass, $event) {
                    $this->triggerPortEvent($port, $handlerClass, 'on' . $event, func_get_args());
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
            throw new \InvalidArgumentException(sprintf('%s must implement the interface %s', get_class($t), WebSocketHandlerInterface::class));
        }
        $handler = $t;
        return $handler;
    }

    protected function getSocketHandler(Port $port, $handlerClass)
    {
        static $handlers = [];
        $portHash = spl_object_hash($port);
        if (isset($handlers[$portHash])) {
            return $handlers[$portHash];
        }
        $t = new $handlerClass($port);
        if (!($t instanceof PortInterface)) {
            throw new \InvalidArgumentException(sprintf('%s must extend the abstract class TcpSocket/UdpSocket', get_class($t)));
        }
        $handlers[$portHash] = $t;
        return $handlers[$portHash];
    }

    protected function bindSwooleTables()
    {
        $tables = isset($this->conf['swoole_tables']) ? (array)$this->conf['swoole_tables'] : [];
        foreach ($tables as $name => $table) {
            $t = new Table($table['size']);
            foreach ($table['column'] as $column) {
                if (isset($column['size'])) {
                    $t->column($column['name'], $column['type'], $column['size']);
                } else {
                    $t->column($column['name'], $column['type']);
                }
            }
            $t->create();
            $name .= 'Table'; // Avoid naming conflicts
            $this->swoole->{$name} = $t;
        }
    }

    public function onStart(HttpServer $server)
    {
        $this->setProcessTitle(sprintf('%s laravels: master process', $this->conf['process_prefix']));

        if (version_compare(SWOOLE_VERSION, '1.9.5', '<')) {
            file_put_contents($this->conf['swoole']['pid_file'], $server->master_pid);
        }
    }

    public function onShutdown(HttpServer $server)
    {
    }

    public function onManagerStart(HttpServer $server)
    {
        $this->setProcessTitle(sprintf('%s laravels: manager process', $this->conf['process_prefix']));
    }

    public function onManagerStop(HttpServer $server)
    {
    }

    public function onWorkerStart(HttpServer $server, $workerId)
    {
        $processName = $workerId >= $server->setting['worker_num'] ? 'task worker' : 'worker';
        $this->setProcessTitle(sprintf('%s laravels: %s process %d', $this->conf['process_prefix'], $processName, $workerId));

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        clearstatcache();

        // Disable Hook
        class_exists('Swoole\Runtime') && \Swoole\Runtime::enableCoroutine(false);
    }

    public function onWorkerStop(HttpServer $server, $workerId)
    {
    }

    public function onWorkerError(HttpServer $server, $workerId, $workerPId, $exitCode, $signal)
    {
        $this->error(sprintf('worker[%d] error: exitCode=%s, signal=%s', $workerId, $exitCode, $signal));
    }

    public function onPipeMessage(HttpServer $server, $srcWorkerId, $message)
    {
        if ($message instanceof BaseTask) {
            $this->onTask($server, null, $srcWorkerId, $message);
        }
    }

    public function onRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse)
    {
    }

    public function onHandShake(SwooleRequest $request, SwooleResponse $response)
    {
        if (!isset($request->header['sec-websocket-key'])) {
            // Bad protocol implementation: it is not RFC6455.
            $response->end();
            return;
        }
        $secKey = $request->header['sec-websocket-key'];
        if (!preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $secKey) || 16 !== strlen(base64_decode($secKey))) {
            // Header Sec-WebSocket-Key is illegal;
            $response->end();
            return;
        }

        $headers = [
            'Upgrade'               => 'websocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Accept'  => base64_encode(sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true)),
            'Sec-WebSocket-Version' => '13',
        ];

        // WebSocket connection to 'ws://127.0.0.1:5200/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        $response->status(101);
        $response->end();
    }

    public function onTask(HttpServer $server, $taskId, $srcWorkerId, $data)
    {
        if ($data instanceof Event) {
            $this->handleEvent($data);
        } elseif ($data instanceof Task) {
            if ($this->handleTask($data) && method_exists($data, 'finish')) {
                return $data;
            }
        }
    }

    public function onFinish(HttpServer $server, $taskId, $data)
    {
        if ($data instanceof Task) {
            $data->finish();
        }
    }

    protected function handleEvent(Event $event)
    {
        $listenerClasses = $event->getListeners();
        foreach ($listenerClasses as $listenerClass) {
            /**@var Listener $listener */
            $listener = new $listenerClass($event);
            if (!($listener instanceof Listener)) {
                throw new \InvalidArgumentException(sprintf('%s must extend the abstract class %s', $listenerClass, Listener::class));
            }
            $this->callWithCatchException(function () use ($listener) {
                $listener->handle();
            }, [], $event->getTries());
        }
        return true;
    }

    protected function handleTask(Task $task)
    {
        return $this->callWithCatchException(function () use ($task) {
            $task->handle();
            return true;
        }, [], $task->getTries());
    }

    protected function fireEvent($event, $interface, array $arguments)
    {
        if (isset($this->conf['event_handlers'][$event])) {
            $eventHandlers = (array)$this->conf['event_handlers'][$event];
            foreach ($eventHandlers as $eventHandler) {
                if (!isset(class_implements($eventHandler)[$interface])) {
                    throw new \InvalidArgumentException(sprintf(
                            '%s must implement the interface %s',
                            $eventHandler,
                            $interface
                        )
                    );
                }
                $this->callWithCatchException(function () use ($eventHandler, $arguments) {
                    call_user_func_array([(new $eventHandler), 'handle'], $arguments);
                });
            }
        }
    }

    public function run()
    {
        $this->swoole->start();
    }
}
