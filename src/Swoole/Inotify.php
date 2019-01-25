<?php

namespace Hhxsv5\LaravelS\Swoole;

class Inotify
{
    private $fd;
    private $watchPath;
    private $watchMask;
    private $watchHandler;
    private $doing        = false;
    private $fileTypes    = [];
    private $excludedDirs = [];
    private $wdPath       = [];
    private $pathWd       = [];

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

    public function addExcludedDir($dir)
    {
        $dir = realpath($dir);
        $this->excludedDirs[$dir] = $dir;
    }

    public function addExcludedDirs(array $dirs)
    {
        foreach ($dirs as $dir) {
            $this->addExcludedDir($dir);
        }
    }

    public function isExcluded($path)
    {
        foreach ($this->excludedDirs as $excludedDir) {
            if ($excludedDir === $path || strpos($path, $excludedDir . '/') === 0) {
                return true;
            }
        }
        return false;
    }

    public function watch()
    {
        $this->_watch($this->watchPath);
    }

    protected function _watch($path)
    {
        if ($this->isExcluded($path)) {
            return false;
        }
        $wd = inotify_add_watch($this->fd, $path, $this->watchMask);
        if ($wd === false) {
            return false;
        }
        $this->bind($wd, $path);

        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $this->isExcluded($file)) {
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
            @inotify_rm_watch($this->fd, $wd);
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
        swoole_event_add($this->fd, function ($fp) {
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
