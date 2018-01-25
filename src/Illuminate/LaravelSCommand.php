<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Console\Command;

class LaravelSCommand extends Command
{
    protected $signature = 'laravels {action?}';

    protected $description = 'LaravelS Console Tool';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $this->handle();
    }

    public function handle()
    {
        $action = $this->argument('action');
        switch ($action) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'reload':
                $this->reload();
                break;
            default:
                $this->info('php laravels {start|stop|restart|reload}');
        }
    }

    protected function start()
    {
        $laravelConf = ['rootPath' => base_path()];
        $svrConf = config('laravels');
        if (file_exists($svrConf['swoole']['pid_file'])) {
            $pid = file_get_contents($svrConf['swoole']['pid_file']);
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
            $pid = file_get_contents($pidFile);
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

        $pid = file_get_contents($pidFile);
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
}