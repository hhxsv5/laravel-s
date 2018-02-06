<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class LaravelSCommand extends Command
{
    protected $signature = 'laravels';

    protected $description = 'LaravelS Console Tool';

    protected $actions;

    protected $isLumen = false;

    public function __construct()
    {
        $this->actions = ['start', 'stop', 'restart', 'reload', 'publish'];
        $actions = implode('|', $this->actions);
        $this->signature .= sprintf(' {action : %s}', $actions);
        $this->description .= ': ' . $actions;

        parent::__construct();
    }

    public function fire()
    {
        $this->handle();
    }

    public function handle()
    {
        $action = $this->argument('action');
        if (!in_array($action, $this->actions, true)) {
            $this->warn(sprintf('LaravelS: action %s is not available, only support %s', $action, implode('|', $this->actions)));
            return;
        }
        $this->info('LaravelS <comment>Speed up your Laravel/Lumen</comment>');
        $this->table(['Component', 'Version'], [
            ['Component' => 'PHP', 'Version' => phpversion()],
            ['Component' => 'Swoole', 'Version' => \swoole_version()],
            ['Component' => $this->getApplication()->getName(), 'Version' => $this->getApplication()->getVersion()],
        ]);

        $this->isLumen = stripos($this->getApplication()->getVersion(), 'Lumen') !== false;
        $this->loadConfigManually();
        $this->{$action}();
    }

    protected function loadConfigManually()
    {
        // Load configuration laravel.php manually for Lumen
        if ($this->isLumen && file_exists(base_path('config/laravels.php'))) {
            $this->getLaravel()->configure('laravels');
        }
    }

    protected function start()
    {
        $svrConf = config('laravels');
        if (empty($svrConf['swoole']['document_root'])) {
            $svrConf['swoole']['document_root'] = base_path('public');
        }
        $laravelConf = [
            'rootPath'   => base_path(),
            'staticPath' => $svrConf['swoole']['document_root'],
            'isLumen'    => $this->isLumen,
        ];

        if (file_exists($svrConf['swoole']['pid_file'])) {
            $pid = (int)file_get_contents($svrConf['swoole']['pid_file']);
            if ($this->killProcess($pid, 0)) {
                $this->warn("LaravelS: PID[{$pid}] is already running.");
                return;
            }
        }

        // Implements gracefully reload, avoid including laravel's files before worker start
        $cmd = sprintf('%s %s/../GoLaravelS.php', PHP_BINARY, __DIR__);
        $fp = popen($cmd, 'w');
        if (!$fp) {
            $this->error('LaravelS: popen ' . $cmd . ' failed');
            return;
        }
        fwrite($fp, json_encode(compact('svrConf', 'laravelConf')));
        fclose($fp);
        $pidFile = config('laravels.swoole.pid_file');

        // Make sure that master process started
        $time = 0;
        while (!file_exists($pidFile) && $time <= 20) {
            usleep(100000);
            $time++;
        }
        if (file_exists($pidFile)) {
            $this->info(sprintf('LaravelS: PID[%s] is running.', file_get_contents($pidFile)));
        } else {
            $this->error(sprintf('LaravelS: PID file[%s] does not exist.', $pidFile));
        }
    }

    protected function stop($ignoreErr = false)
    {
        $pidFile = config('laravels.swoole.pid_file');
        if (file_exists($pidFile)) {
            $pid = (int)file_get_contents($pidFile);
            if ($this->killProcess($pid, 0)) {
                if ($this->killProcess($pid, SIGTERM)) {
                    // Make sure that master process quit
                    $time = 0;
                    while ($this->killProcess($pid, 0) && $time <= 20) {
                        usleep(100000);
                        $time++;
                    }
                    if (file_exists($pidFile)) {
                        unlink($pidFile);
                    }
                    $this->info("LaravelS: PID[{$pid}] is stopped.");
                } else {
                    $this->error("LaravelS: PID[{$pid}] is stopped failed.");
                }
            } else {
                $this->warn("LaravelS: PID[{$pid}] does not exist, or permission denied.");
                if (file_exists($pidFile)) {
                    unlink($pidFile);
                }
                if (!$ignoreErr) {
                    return;
                }
            }
        } else {
            $this->info('LaravelS: already stopped.');
        }
    }

    protected function restart()
    {
        $this->stop(true);
        $this->start();
    }

    protected function reload()
    {
        $pidFile = config('laravels.swoole.pid_file');
        if (!file_exists($pidFile)) {
            $this->error('LaravelS: it seems that LaravelS is not running.');
            return;
        }

        $pid = (int)file_get_contents($pidFile);
        if (!$this->killProcess($pid, 0)) {
            $this->error("LaravelS: PID[{$pid}] does not exist, or permission denied.");
            return;
        }

        if ($this->killProcess($pid, SIGUSR1)) {
            $this->info("LaravelS: PID[{$pid}] is reloaded.");
        } else {
            $this->error("LaravelS: PID[{$pid}] is reloaded failed.");
        }
    }

    protected function publish()
    {
        $to = base_path('config/laravels.php');
        if (file_exists($to)) {
            $choice = $this->anticipate($to . ' already exists, do you want to override it ? Y/N', ['Y', 'N'], 'N');
            if (!$choice || strtoupper($choice) !== 'Y') {
                $this->info('Publishing skipped.');
                return;
            }
        }

        try {
            $this->call('vendor:publish', ['--provider' => LaravelSServiceProvider::class, '--force' => true]);
            return;
        } catch (\Exception $e) {
            if (!($e instanceof \InvalidArgumentException)) {
                throw $e;
            }
        }
        $from = __DIR__ . '/../Config/laravels.php';

        $toDir = dirname($to);


        /**
         * @var Filesystem $files
         */
        $files = app(Filesystem::class);

        if (!$files->isDirectory($toDir)) {
            $files->makeDirectory($toDir, 0755, true);
        }

        $files->copy($from, $to);

        $from = str_replace(base_path(), '', realpath($from));

        $to = str_replace(base_path(), '', realpath($to));

        $this->line('<info>Copied File</info> <comment>[' . $from . ']</comment> <info>To</info> <comment>[' . $to . ']</comment>');
    }

    protected function killProcess($pid, $sig)
    {
        try {
            return \swoole_process::kill($pid, $sig);
        } catch (\Exception $e) {
            return false;
        }
    }
}
