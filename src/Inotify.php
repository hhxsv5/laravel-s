<?php

namespace Hhxsv5\LaravelS;

class Inotify
{
    private $fd              = null;
    private $reloading       = false;
    private $reloadFileTypes = ['.php' => true];

    private $wdHandler = [];

    public function __construct()
    {
        $this->fd = inotify_init();
    }

    public function addFileType($type)
    {
        $type = '.' . trim($type, '.');
        $this->reloadFileTypes[$type] = true;
    }

    public function on($path, $mask, callable $handler)
    {
        $wd = inotify_add_watch($this->fd, $path, $mask);
        $this->wdHandler[$wd] = $handler;
    }

    public function run()
    {
        swoole_event_add($this->fd, function ($fp) {
            echo 'read...', PHP_EOL;
            $events = inotify_read($this->fd);
            var_dump($events);
            foreach ($events as $event) {
                if ($this->reloading) {
                    continue;
                }

                if ($event['mask'] == IN_IGNORED) {
                    continue;
                }

                $fileType = strchr($event['name'], '.');
                if (!isset($this->reloadFileTypes[$fileType])) {
                    continue;
                }

                $this->reloading = true;
                call_user_func_array($this->wdHandler[$event['wd']], [$event['name']]);
                $this->reloading = false;
                echo 'after reload', PHP_EOL;
            }
        });
    }
}