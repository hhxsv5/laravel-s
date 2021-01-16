<?php

namespace Hhxsv5\LaravelS\Swoole;

use Hhxsv5\LaravelS\Console\Portal;
use Swoole\Http\Server;
use Swoole\Process;

trait InotifyTrait
{
    public function addInotifyProcess(Server $swoole, array $config, array $laravelConf)
    {
        if (empty($config['enable'])) {
            return false;
        }

        if (!extension_loaded('inotify')) {
            $this->warning('Require extension inotify');
            return false;
        }

        $fileTypes = isset($config['file_types']) ? (array)$config['file_types'] : [];
        if (empty($fileTypes)) {
            $this->warning('No file types to watch by inotify');
            return false;
        }

        $callback = function () use ($config, $laravelConf) {
            $log = !empty($config['log']);
            $this->setProcessTitle(sprintf('%s laravels: inotify process', $config['process_prefix']));
            $inotify = new Inotify($config['watch_path'], IN_CREATE | IN_DELETE | IN_MODIFY | IN_MOVE,
                function ($event) use ($log, $laravelConf) {
                    Portal::runLaravelSCommand($laravelConf['root_path'], 'reload');
                    if ($log) {
                        $action = 'file:';
                        switch ($event['mask']) {
                            case IN_CREATE:
                                $action = 'create';
                                break;
                            case IN_DELETE:
                                $action = 'delete';
                                break;
                            case IN_MODIFY:
                                $action = 'modify';
                                break;
                            case IN_MOVE:
                                $action = 'move';
                                break;
                        }
                        $this->info(sprintf('reloaded by inotify, reason: %s %s', $action, $event['name']));
                    }
                });
            $inotify->addFileTypes($config['file_types']);
            if (empty($config['excluded_dirs'])) {
                $config['excluded_dirs'] = [];
            }
            $inotify->addExcludedDirs($config['excluded_dirs']);
            $inotify->watch();
            if ($log) {
                $this->info(sprintf('[Inotify] watched files: %d; file types: %s; excluded directories: %s',
                        $inotify->getWatchedFileCount(),
                        implode(',', $config['file_types']),
                        implode(',', $config['excluded_dirs'])
                    )
                );
            }
            $inotify->start();
        };

        $process = new Process($callback, false, 0);
        $swoole->addProcess($process);
        return $process;
    }
}