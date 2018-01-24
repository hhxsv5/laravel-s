<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\LaravelS;
use Illuminate\Console\Command;

class LaravelSCommand extends Command
{
    protected $signature = 'laravels {action : start|stop|reload}';

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
            case 'reload':
                $this->reload();
                break;
            default:
                $this->info('laravels {action : start|stop|reload}');
        }
    }

    protected function start()
    {
        $laravelConf = ['rootPath' => base_path()];
        $svrConf = config('laravels');
        if (file_exists($svrConf['swoole']['pid_file'])) {
            $pid = file_get_contents($svrConf['swoole']['pid_file']);
            if (posix_kill($pid, 0)) {
                $this->warn("LaravelS: PID[{$pid}] already running.");
                return;
            }
        }

        $s = LaravelS::getInstance($svrConf, $laravelConf);
        $s->run();
    }

    protected function stop()
    {
        $pidFile = config('laravels.swoole.pid_file');
        if (!file_exists($pidFile)) {
            $this->info('LaravelS: already stopped.');
            return;
        }

        $pid = file_get_contents($pidFile);
        if (!posix_kill($pid, 0)) {
            $this->warn("LaravelS: PID[{$pid}] does not exist, or permission denied.");
            return;
        }

        if (posix_kill($pid, SIGTERM)) {
            $this->info("LaravelS: PID[{$pid}] has been stopped successfully.");
        } else {
            $this->error("LaravelS: PID[{$pid}] has been stopped failed.");
        }
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
            $this->info("LaravelS: PID[{$pid}] has been reloaded successfully.");
        } else {
            $this->error("LaravelS: PID[{$pid}] has been reloaded failed.");
        }
    }
}