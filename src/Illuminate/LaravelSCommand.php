<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class LaravelSCommand extends Command
{
    protected $signature = 'laravels {action? : publish|config|info}
    {--d|daemonize : Whether run as a daemon for "start & restart"}
    {--i|ignore : Whether ignore checking process pid for "start & restart"}';

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
                $this->prepareConfig();
                break;
            case 'info':
                $this->showInfo();
                break;
            default:
                $this->info('Usage: php artisan laravels publish|config|info');
                break;
        }
    }

    protected function isLumen()
    {
        return stripos($this->getApplication()->getVersion(), 'Lumen') !== false;
    }

    protected function loadConfigManually()
    {
        // Load configuration laravel.php manually for Lumen
        $basePath = config('laravels.laravel_base_path') ?: base_path();
        if ($this->isLumen() && file_exists($basePath . '/config/laravels.php')) {
            $this->getLaravel()->configure('laravels');
        }
    }

    protected function showInfo()
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
        $this->comment('Speed up your Laravel/Lumen');
        $this->table(['Component', 'Version'], [
            [
                'Component' => 'PHP',
                'Version'   => phpversion(),
            ],
            [
                'Component' => 'Swoole',
                'Version'   => swoole_version(),
            ],
            [
                'Component' => $this->getApplication()->getName(),
                'Version'   => $this->getApplication()->getVersion(),
            ],
        ]);
    }

    protected function prepareConfig()
    {
        $this->loadConfigManually();

        $svrConf = config('laravels');

        $this->preSet($svrConf);

        $ret = $this->preCheck($svrConf);
        if ($ret !== 0) {
            return $ret;
        }

        $laravelConf = [
            'root_path'          => $svrConf['laravel_base_path'],
            'static_path'        => $svrConf['swoole']['document_root'],
            'register_providers' => array_unique((array)array_get($svrConf, 'register_providers', [])),
            'is_lumen'           => $this->isLumen(),
            '_SERVER'            => $_SERVER,
            '_ENV'               => $_ENV,
        ];

        $config = json_encode(compact('svrConf', 'laravelConf'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents(base_path('storage/laravels.json'), $config);
        $this->info('Prepare configuration successfully');
        return 0;
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
        if (empty($svrConf['swoole']['document_root'])) {
            $svrConf['swoole']['document_root'] = $svrConf['laravel_base_path'] . '/public';
        }
        if ($this->option('daemonize')) {
            $svrConf['swoole']['daemonize'] = true;
        }
        if (empty($svrConf['swoole']['pid_file'])) {
            $svrConf['swoole']['pid_file'] = storage_path('laravels.pid');
        }
        return 0;
    }

    protected function preCheck(array $svrConf)
    {
        if (!empty($svrConf['enable_gzip']) && version_compare(swoole_version(), '4.1.0', '>=')) {
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
            ['from' => __DIR__ . '/../../config/laravels.php', 'to' => $configPath, 'mode' => 0644],
            ['from' => __DIR__ . '/../../bin/laravels', 'to' => $basePath . '/bin/laravels', 'mode' => 0755],
            ['from' => __DIR__ . '/../../bin/fswatch', 'to' => $basePath . '/bin/fswatch', 'mode' => 0755],
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

        /** @var Filesystem $file */
        $file = app(Filesystem::class);
        foreach ($todoList as $todo) {
            $toDir = dirname($todo['to']);
            if (!$file->isDirectory($toDir)) {
                $file->makeDirectory($toDir, 0755, true);
            }
            $file->copy($todo['from'], $todo['to']);
            chmod($todo['to'], $todo['mode']);
            $from = str_replace($basePath, '', realpath($todo['from']));
            $to = str_replace($basePath, '', realpath($todo['to']));
            $this->line('<info>Copied File</info> <comment>[' . $from . ']</comment> <info>To</info> <comment>[' . $to . ']</comment>');
        }
        return 0;
    }
}
