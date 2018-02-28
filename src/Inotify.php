<?php

namespace Hhxsv5\LaravelS;

class Inotify
{
    private $fd;
    private $reloading       = false;
    private $reloadFileTypes = ['.php' => true];
    private $wdHandler       = [];
    private $wdMask          = [];
    private $wdPath          = [];
    private $pathWd          = [];

    public function __construct()
    {
        $this->fd = inotify_init();
    }

    public function addFileType($type)
    {
        $type = '.' . trim($type, '.');
        $this->reloadFileTypes[$type] = true;
    }

    public function addFileTypes(array $types)
    {
        foreach ($types as $type) {
            $this->addFileType($type);
        }
    }

    public function on($path, $mask, callable $handler)
    {
        if (isset($this->pathWd[$path])) {
            return false;
        }

        $wd = inotify_add_watch($this->fd, $path, $mask);
        $this->bind($wd, $path, $handler, $mask);

        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $file = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($file)) {
                    $this->on($file, $mask, $handler);
                }

                $fileType = strrchr($file, '.');
                if (isset($this->reloadFileTypes[$fileType])) {
                    var_dump($file);
                    $wd = inotify_add_watch($this->fd, $file, $mask);
                    $this->bind($wd, $file, $handler, $mask);
                }
            }
        }
        return true;
    }

    protected function bind($wd, $path, $handler, $mask)
    {
        $this->pathWd[$path] = $wd;
        $this->wdHandler[$wd] = $handler;
        $this->wdMask[$wd] = $mask;
        $this->wdPath[$wd] = $path;
    }

    protected function unbind($wd)
    {
        unset($this->wdHandler[$wd], $this->wdMask[$wd], $this->wdPath[$wd]);
    }

    public function start()
    {
        swoole_event_add($this->fd, function ($fp) {
            $events = inotify_read($this->fd);
//            var_dump($events);
            foreach ($events as $event) {
                if ($this->reloading) {
                    continue;
                }

//                if ($event['mask'] == IN_IGNORED) {
//                    continue;
//                }
//
//                $fileType = strchr($event['name'], '.');
//                if (!isset($this->reloadFileTypes[$fileType])) {
//                    continue;
//                }

                $this->reloading = true;
                call_user_func_array($this->wdHandler[$event['wd']], [$event]);
                $this->reloading = false;

                $wd = inotify_add_watch($this->fd, $this->wdPath[$event['wd']], $this->wdMask[$event['wd']]);
                $this->bind($wd, $this->wdPath[$event['wd']], $this->wdHandler[$event['wd']], $this->wdMask[$event['wd']]);
                $this->unbind($event['wd']);
            }
        });
        swoole_event_wait();
    }

    public function stop()
    {
        swoole_event_del($this->fd);
        fclose($this->fd);
    }

    public function __destruct()
    {
        $this->stop();
    }
}