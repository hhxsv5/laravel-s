<?php

namespace Hhxsv5\LaravelS;

class Inotify
{
    private $fd;
    private $watchPath;
    private $watchMask;
    private $watchHandler;
    private $reloading       = false;
    private $reloadFileTypes = ['.php' => true];
    private $wdPath          = [];
    private $pathWd          = [];

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
        $this->reloadFileTypes[$type] = true;
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
                if (isset($this->reloadFileTypes[$fileType])) {
                    $wd = inotify_add_watch($this->fd, $file, $this->watchMask);
                    $this->bind($wd, $file);
                }
            }
        }
        return true;
    }

    protected function clearWatch()
    {
        foreach ($this->wdPath as $wd => $path) {
            inotify_rm_watch($this->fd, $wd);
            $this->unbind($wd, $path);
        }
    }

    protected function bind($wd, $path)
    {
        $this->pathWd[$path] = $wd;
        $this->wdPath[$wd] = $path;
    }

    protected function unbind($wd, $path)
    {
        unset($this->wdPath[$wd], $this->pathWd[$path]);
    }

    public function start()
    {
        swoole_event_add($this->fd, function ($fp) {
            $events = inotify_read($this->fd);
            foreach ($events as $event) {
                if ($event['mask'] == IN_IGNORED) {
                    continue;
                }

                $fileType = strchr($event['name'], '.');
                if (!isset($this->reloadFileTypes[$fileType])) {
                    continue;
                }

                if ($this->reloading) {
                    continue;
                }
                $this->reloading = true;

                // Clear watch to avoid multiple events
                $this->clearWatch();
                call_user_func_array($this->watchHandler, [$event]);

                // Watch again
                $this->watch();
                $this->reloading = false;
            }
        });
        swoole_event_wait();
    }

    public function stop()
    {
        swoole_event_del($this->fd);
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