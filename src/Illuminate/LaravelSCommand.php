<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class LaravelSCommand extends Command
{
    protected $signature = 'laravels {action?}';

    protected $description = 'LaravelS Console Tool';

    protected $actions;

    protected $isLumen = false;

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->actions = ['start', 'stop', 'restart', 'reload', 'publish'];
        $this->description .= ': ' . implode('|', $this->actions);
        $this->files = $files;
    }

    public function fire()
    {
        $this->handle();
    }

    public function handle()
    {
        $action = $this->argument('action');
        if (!in_array($action, $this->actions, true)) {
            $this->info('php laravels {' . implode('|', $this->actions) . '}');
            return;
        }
        $this->info('LaravelS for ' . $this->getApplication()->getLongVersion());
        $this->isLumen = stripos($this->getApplication()->getVersion(), 'Lumen') !== false;
        $this->{$action}();
    }

    protected function start()
    {
        $laravelConf = ['rootPath' => base_path(), 'isLumen' => $this->isLumen];
        $svrConf = config('laravels');
        if (file_exists($svrConf['swoole']['pid_file'])) {
            $pid = (int)file_get_contents($svrConf['swoole']['pid_file']);
            if (posix_kill($pid, 0)) {
                $this->warn("LaravelS: PID[{$pid}] is already running.");
                return;
            }
        }

        // Implements gracefully reload, avoid including laravel's files before worker start
        $cmd = sprintf('%s %s/../GoLaravelS.php', PHP_BINARY, __DIR__);
        $fp = popen($cmd, 'w');
        fwrite($fp, json_encode(compact('svrConf', 'laravelConf')));
        fclose($fp);
        $pidFile = config('laravels.swoole.pid_file');
        $this->info(sprintf('LaravelS: PID[%s] is running.', file_get_contents($pidFile)));
    }

    protected function stop($ignoreErr = false)
    {
        $pidFile = config('laravels.swoole.pid_file');
        if (file_exists($pidFile)) {
            $pid = (int)file_get_contents($pidFile);
            if (posix_kill($pid, 0)) {
                if (posix_kill($pid, SIGTERM)) {
                    // Make sure that master process quit
                    $time = 0;
                    while (posix_getpgid($pid) && $time <= 20) {
                        usleep(100000);
                        $time++;
                    }
                    $this->info("LaravelS: PID[{$pid}] is stopped.");
                } else {
                    $this->error("LaravelS: PID[{$pid}] is stopped failed.");
                }
            } else {
                $this->warn("LaravelS: PID[{$pid}] does not exist, or permission denied.");
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
        if (!posix_kill($pid, 0)) {
            $this->error("LaravelS: PID[{$pid}] does not exist, or permission denied.");
            return;
        }

        if (posix_kill($pid, SIGUSR1)) {
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
            if (!$choice || strtoupper($choice) === 'N') {
                $this->info('Publishing complete.');
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

        if (!$this->files->isDirectory($toDir)) {
            $this->files->makeDirectory($toDir, 0755, true);
        }

        $this->files->copy($from, $to);

        $from = str_replace(base_path(), '', realpath($from));

        $to = str_replace(base_path(), '', realpath($to));

        $this->line('<info>Copied File</info> <comment>[' . $from . ']</comment> <info>To</info> <comment>[' . $to . ']</comment>');
    }

    public function getDescription()
    {
        return $this->description;
    }
}