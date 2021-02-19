<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class LaravelSCommand extends Command
{
    protected $signature = 'laravels {action? : publish|config|info}
    {--d|daemonize : Run as a daemon}
    {--i|ignore : Ignore checking PID file of Master process}
    {--x=|x-version= : The version(branch) of the current project, stored in $_ENV/$_SERVER}';

    protected $description = 'LaravelS console tool';

    public function fire()
    {
        $this->handle();
    }

    public function handle()
    {
        $action = (string)$this->argument('action');
        switch ($action) {
            case 'publish':
                $this->publish();
                break;
            case 'config':
            case 'info':
                $this->prepareConfig();
                $this->showInfo();
                break;
            default:
                $this->info(sprintf('Usage: [%s] ./artisan laravels publish|config|info', PHP_BINARY));
                if (in_array($action, ['start', 'stop', 'restart', 'reload'], true)) {
                    $this->error(sprintf(
                        'The "%s" command has been migrated to "bin/laravels", %ssee https://github.com/hhxsv5/laravel-s#run',
                        $action,
                        file_exists(base_path('bin/laravels')) ? '' : 'please run `php artisan laravels publish` first, '
                    ));
                }
                break;
        }
    }

    protected function isLumen()
    {
        return stripos($this->getApplication()->getVersion(), 'Lumen') !== false;
    }

    protected function loadConfig()
    {
        // Load configuration laravel.php manually for Lumen
        $basePath = config('laravels.laravel_base_path') ?: base_path();
        if ($this->isLumen() && file_exists($basePath . '/config/laravels.php')) {
            $this->getLaravel()->configure('laravels');
        }
    }

    protected function showInfo()
    {
        $this->showLogo();
        $this->showComponents();
        $this->showProtocols();
        $this->comment('>>> Feedback: <options=underscore>https://github.com/hhxsv5/laravel-s</>');
    }

    protected function showLogo()
    {
        static $logo = <<<EOS
 _                               _  _____ 
| |                             | |/ ____|
| |     __ _ _ __ __ ___   _____| | (___  
| |    / _` | '__/ _` \ \ / / _ \ |\___ \ 
| |___| (_| | | | (_| |\ V /  __/ |____) |
|______\__,_|_|  \__,_| \_/ \___|_|_____/ 
                                           
EOS;
        $this->info($logo);
        $this->info('Speed up your Laravel/Lumen');
    }

    protected function showComponents()
    {
        $this->comment('>>> Components');
        $laravelSVersion = '-';
        $lockFile = base_path('composer.lock');
        $cfg = file_exists($lockFile) ? json_decode(file_get_contents($lockFile), true) : [];
        if (isset($cfg['packages'])) {
            $packages = array_merge($cfg['packages'], Arr::get($cfg, 'packages-dev', []));
            foreach ($packages as $package) {
                if (isset($package['name']) && $package['name'] === 'hhxsv5/laravel-s') {
                    $laravelSVersion = ltrim($package['version'], 'vV');
                    break;
                }
            }
        }
        $this->table(['Component', 'Version'], [
            [
                'PHP',
                PHP_VERSION,
            ],
            [
                'Swoole',
                SWOOLE_VERSION,
            ],
            [
                'LaravelS',
                $laravelSVersion,
            ],
            [
                $this->getApplication()->getName() . ' [<info>' . env('APP_ENV', config('app.env')) . '</info>]',
                $this->getApplication()->getVersion(),
            ],
        ]);
    }

    protected function showProtocols()
    {
        $this->comment('>>> Protocols');

        $config = unserialize((string)file_get_contents($this->getConfigPath()));
        $ssl = isset($config['server']['swoole']['ssl_key_file'], $config['server']['swoole']['ssl_cert_file']);
        $socketType = isset($config['server']['socket_type']) ? $config['server']['socket_type'] : SWOOLE_SOCK_TCP;
        if (in_array($socketType, [SWOOLE_SOCK_UNIX_DGRAM, SWOOLE_SOCK_UNIX_STREAM])) {
            $listenAt = $config['server']['listen_ip'];
        } else {
            $listenAt = sprintf('%s:%s', $config['server']['listen_ip'], $config['server']['listen_port']);
        }

        $tableRows = [
            [
                'Main HTTP',
                '<info>On</info>',
                $this->getApplication()->getName(),
                sprintf('%s://%s', $ssl ? 'https' : 'http', $listenAt),
            ],
        ];
        if (!empty($config['server']['websocket']['enable'])) {
            $tableRows [] = [
                'Main WebSocket',
                '<info>On</info>',
                $config['server']['websocket']['handler'],
                sprintf('%s://%s', $ssl ? 'wss' : 'ws', $listenAt),
            ];
        }

        $socketTypeNames = [
            SWOOLE_SOCK_TCP         => 'TCP IPV4 Socket',
            SWOOLE_SOCK_TCP6        => 'TCP IPV6 Socket',
            SWOOLE_SOCK_UDP         => 'UDP IPV4 Socket',
            SWOOLE_SOCK_UDP6        => 'TCP IPV6 Socket',
            SWOOLE_SOCK_UNIX_DGRAM  => 'Unix Socket Dgram',
            SWOOLE_SOCK_UNIX_STREAM => 'Unix Socket Stream',
        ];
        $sockets = isset($config['server']['sockets']) ? $config['server']['sockets'] : [];
        foreach ($sockets as $key => $socket) {
            if (isset($socket['enable']) && !$socket['enable']) {
                continue;
            }

            $name = 'Port#' . $key . ' ';
            $name .= isset($socketTypeNames[$socket['type']]) ? $socketTypeNames[$socket['type']] : 'Unknown socket';
            $tableRows [] = [
                $name,
                '<info>On</info>',
                $socket['handler'],
                sprintf('%s:%s', $socket['host'], $socket['port']),
            ];
        }
        $this->table(['Protocol', 'Status', 'Handler', 'Listen At'], $tableRows);
    }

    protected function prepareConfig()
    {
        $this->loadConfig();

        $svrConf = config('laravels');

        $this->preSet($svrConf);

        $ret = $this->preCheck($svrConf);
        if ($ret !== 0) {
            return $ret;
        }

        // Fixed $_ENV['APP_ENV']
        if (isset($_SERVER['APP_ENV'])) {
            $_ENV['APP_ENV'] = $_SERVER['APP_ENV'];
        }

        $laravelConf = [
            'root_path'           => $svrConf['laravel_base_path'],
            'static_path'         => $svrConf['swoole']['document_root'],
            'cleaners'            => array_unique((array)Arr::get($svrConf, 'cleaners', [])),
            'register_providers'  => array_unique((array)Arr::get($svrConf, 'register_providers', [])),
            'destroy_controllers' => Arr::get($svrConf, 'destroy_controllers', []),
            'is_lumen'            => $this->isLumen(),
            '_SERVER'             => $_SERVER,
            '_ENV'                => $_ENV,
        ];

        $config = ['server' => $svrConf, 'laravel' => $laravelConf];
        return file_put_contents($this->getConfigPath(), serialize($config)) > 0 ? 0 : 1;
    }

    protected function getConfigPath()
    {
        return storage_path('laravels.conf');
    }

    protected function preSet(array &$svrConf)
    {
        if (!isset($svrConf['enable_gzip'])) {
            $svrConf['enable_gzip'] = false;
        }
        if (empty($svrConf['laravel_base_path'])) {
            $svrConf['laravel_base_path'] = base_path();
        }
        if (empty($svrConf['process_prefix'])) {
            $svrConf['process_prefix'] = $svrConf['laravel_base_path'];
        }
        if ($this->option('ignore')) {
            $svrConf['ignore_check_pid'] = true;
        } elseif (!isset($svrConf['ignore_check_pid'])) {
            $svrConf['ignore_check_pid'] = false;
        }
        if (empty($svrConf['swoole']['document_root'])) {
            $svrConf['swoole']['document_root'] = $svrConf['laravel_base_path'] . '/public';
        }
        if ($this->option('daemonize')) {
            $svrConf['swoole']['daemonize'] = true;
        } elseif (!isset($svrConf['swoole']['daemonize'])) {
            $svrConf['swoole']['daemonize'] = false;
        }
        if (empty($svrConf['swoole']['pid_file'])) {
            $svrConf['swoole']['pid_file'] = storage_path('laravels.pid');
        }
        if (empty($svrConf['timer']['max_wait_time'])) {
            $svrConf['timer']['max_wait_time'] = 5;
        }

        // Set X-Version
        $xVersion = (string)$this->option('x-version');
        if ($xVersion !== '') {
            $_SERVER['X_VERSION'] = $_ENV['X_VERSION'] = $xVersion;
        }
        return 0;
    }

    protected function preCheck(array $svrConf)
    {
        if (!empty($svrConf['enable_gzip']) && version_compare(SWOOLE_VERSION, '4.1.0', '>=')) {
            $this->error('enable_gzip is DEPRECATED since Swoole 4.1.0, set http_compression of Swoole instead, http_compression is disabled by default.');
            $this->info('If there is a proxy server like Nginx, suggest that enable gzip in Nginx and disable gzip in Swoole, to avoid the repeated gzip compression for response.');
            return 1;
        }
        if (!empty($svrConf['events'])) {
            if (empty($svrConf['swoole']['task_worker_num']) || $svrConf['swoole']['task_worker_num'] <= 0) {
                $this->error('Asynchronous event listening needs to set task_worker_num > 0');
                return 1;
            }
        }
        return 0;
    }


    public function publish()
    {
        $basePath = config('laravels.laravel_base_path') ?: base_path();
        $configPath = $basePath . '/config/laravels.php';
        $todoList = [
            [
                'from' => realpath(__DIR__ . '/../../config/laravels.php'),
                'to'   => $configPath,
                'mode' => 0644,
            ],
            [
                'from' => realpath(__DIR__ . '/../../bin/laravels'),
                'to'   => $basePath . '/bin/laravels',
                'mode' => 0755,
                'link' => true,
            ],
            [
                'from' => realpath(__DIR__ . '/../../bin/fswatch'),
                'to'   => $basePath . '/bin/fswatch',
                'mode' => 0755,
                'link' => true,
            ],
            [
                'from' => realpath(__DIR__ . '/../../bin/inotify'),
                'to'   => $basePath . '/bin/inotify',
                'mode' => 0755,
                'link' => true,
            ],
        ];
        if (file_exists($configPath)) {
            $choice = $this->anticipate($configPath . ' already exists, do you want to override it ? Y/N',
                ['Y', 'N'],
                'N'
            );
            if (!$choice || strtoupper($choice) !== 'Y') {
                array_shift($todoList);
            }
        }

        foreach ($todoList as $todo) {
            $toDir = dirname($todo['to']);
            if (!is_dir($toDir) && !mkdir($toDir, 0755, true) && !is_dir($toDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $toDir));
            }
            if (file_exists($todo['to'])) {
                unlink($todo['to']);
            }
            $operation = 'Copied';
            if (empty($todo['link'])) {
                copy($todo['from'], $todo['to']);
            } elseif (@link($todo['from'], $todo['to'])) {
                $operation = 'Linked';
            } else {
                copy($todo['from'], $todo['to']);
            }
            chmod($todo['to'], $todo['mode']);
            $this->line("<info>{$operation} file</info> <comment>[{$todo['from']}]</comment> <info>To</info> <comment>[{$todo['to']}]</comment>");
        }
        return 0;
    }
}
