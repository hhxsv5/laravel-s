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
                $laravelConf = ['rootPath' => base_path()];
                $svrConf = config('laravels');
                $s = LaravelS::getInstance($laravelConf, $svrConf);
                $s->run();
                $this->info('LaravelS is running...');
                break;
            case 'stop':
                $this->info('stopped');
                break;
            case 'reload':
                $this->info('reloaded');
                break;
        }
    }
}