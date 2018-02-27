<?php

namespace Hhxsv5\LaravelS\Swoole;

class AutoReload
{

    private $inotify;
    private $path;
    private $reloading       = false;
    private $reloadHandler;
    private $reloadFileTypes = ['.php' => true];

    public function __construct($path, callable $reloadHandler)
    {
        $this->path = $path;
        $this->reloadHandler = $reloadHandler;
    }

    public function addReloadFileType($type)
    {
        $type = '.' . trim($type, '.');
        $this->reloadFileTypes[$type] = true;
    }

    public function watch()
    {
        $this->inotify = inotify_init();
        inotify_add_watch($this->inotify, $this->path, IN_CREATE | IN_MODIFY | IN_DELETE | IN_MOVE);
        swoole_event_add($this->inotify, function ($fp) {
            $events = inotify_read($this->inotify);
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
                call_user_func_array($this->reloadHandler, []);
                swoole_event_del($this->inotify);
                $this->reloading = false;
                inotify_rm_watch($this->inotify, $event['wd']);
                fclose($this->inotify);
                $this->watch();
            }
        });
    }
}