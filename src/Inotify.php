<?php

namespace Hhxsv5\LaravelS;

class Inotify
{
    private $fd;
    private $watchPath;
    private $watchMask;
    private $watchHandler;
    private $doing     = false;
    private $fileTypes = ['.php' => true];
    private $wdPath    = [];
    private $pathWd    = [];

    public function __construct($watchPath, $watchMask, callable $watchHandler)
    {
        $this->fd = inotify_init();
        $this->watchPath = $watchPath;
        $this->watchMask = $watchMask;
        $this->watchHandler = $watchHandler;
    }

    public function addFileType($type)
    {
        $type = '.' . trim($type, '.');
        $this->fileTypes[$type] = true;
    }

    public function addFileTypes(array $types)
    {
        foreach ($types as $type) {
            $this->addFileType($type);
        }
    }

    public function watch()
    {
        $this->_watch($this->watchPath);
    }

    protected function _watch($path)
    {
        $wd = inotify_add_watch($this->fd, $path, $this->watchMask);
        if ($wd === false) {
            return false;
        }
        $this->bind($wd, $path);

        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $file = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($file)) {
                    $this->_watch($file);
                }

                $fileType = strrchr($file, '.');
                if (isset($this->fileTypes[$fileType])) {
                    $wd = inotify_add_watch($this->fd, $file, $this->watchMask);
                    if ($wd === false) {
                        return false;
                    }
                    $this->bind($wd, $file);
                }
            }
        }
        return true;
    }

    protected function clearWatch()
    {
        foreach ($this->wdPath as $wd => $path) {
            /** @scrutinizer ignore-unhandled */@inotify_rm_watch($this->fd, $wd);
        }
        $this->wdPath = [];
        $this->pathWd = [];
    }

    protected function bind($wd, $path)
    {
        $this->pathWd[$path] = $wd;
        $this->wdPath[$wd] = $path;
    }

    protected function unbind($wd, $path = null)
    {
        unset($this->wdPath[$wd]);
        if ($path !== null) {
            unset($this->pathWd[$path]);
        }
    }

    public function start()
    {
        swoole_event_add(/** @scrutinizer ignore-type */$this->fd, function ($fp) {
            $events = inotify_read($fp);
            foreach ($events as $event) {
                if ($event['mask'] == IN_IGNORED) {
                    continue;
                }

                $fileType = strchr($event['name'], '.');
                if (!isset($this->fileTypes[$fileType])) {
                    continue;
                }

                if ($this->doing) {
                    continue;
                }

                swoole_timer_after(100, function () use ($event) {
                    call_user_func_array($this->watchHandler, [$event]);
                    $this->doing = false;
                });
                $this->doing = true;
                break;
            }
        });
        swoole_event_wait();
    }

    public function stop()
    {
        swoole_event_del(/** @scrutinizer ignore-type */$this->fd);
        fclose($this->fd);
    }

    public function getWatchedFileCount()
    {
        return count($this->wdPath);
    }

    public function __destruct()
    {
        $this->stop();
    }
}
