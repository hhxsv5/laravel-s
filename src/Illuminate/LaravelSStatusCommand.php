<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Console\Command;

class LaravelSStatusCommand extends Command
{
    protected $signature = "laravels:status";

    protected $description = "Check which uri worker is processing, and how long has been taken";

    public function fire()
    {
        return $this->handle();
    }

    public function handle()
    {
        $file = LaravelSMonitorProcess::sockFile();

        if (file_exists($file)) {
            $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

            socket_connect($socket, $file);

            $status = socket_read($socket, 2048);

            socket_close($socket);

            echo trim($status) . "\n";
        }
    }
}
