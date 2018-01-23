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
        }
    }

    protected function start()
    {
        $laravelConf = ['rootPath' => base_path()];
        $svrConf = config('laravels');
        $s = LaravelS::getInstance($laravelConf, $svrConf);
        $s->run();
    }

    protected function stop()
    {
        $svrConf = config('laravels');
        if (!file_exists($svrConf['pid_file'])) {
            $this->info('LaravelS: stopped.');
            return;
        }

        $pid = file_get_contents($svrConf['pid_file']);
        if (!posix_kill($pid, SIG_DFL)) {
            $this->info('LaravelS: stopped.');
            return;
        }

        posix_kill($pid, SIGTERM);
    }

    protected function reload()
    {
        $svrConf = config('laravels');
        if (!file_exists($svrConf['pid_file'])) {
            $this->error('LaravelS: cannot find pid file.');
            return;
        }

        $pid = file_get_contents($svrConf['pid_file']);
        if (!posix_kill($pid, SIG_DFL)) {
            $this->error("LaravelS: pid[{$pid}] does not exist, or permission denied.");
            return;
        }

        posix_kill($pid, SIGUSR1);
    }
}