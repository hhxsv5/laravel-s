<?php

namespace Hhxsv5\LaravelS\Console;

use Swoole\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class Portal extends Command
{
    protected $basePath;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var InputInterface $input
     */
    protected $input;

    /**
     * @var OutputInterface $output
     */
    protected $output;

    /**
     * @var OutputStyle $output
     */
    protected $outputStyle;

    public function __construct($basePath)
    {
        parent::__construct('laravels');
        $this->basePath = $basePath;
    }

    protected function configure()
    {
        $this->setDescription('LaravelS console tool');
        $this->setHelp('LaravelS console tool');

        $this->addArgument('action', InputArgument::OPTIONAL, 'start|stop|restart|reload|info|help', 'help');
        $this->addOption('daemonize', 'd', InputOption::VALUE_NONE, 'Whether run as a daemon for "start & restart"');
        $this->addOption('ignore', 'i', InputOption::VALUE_NONE, 'Whether ignore checking process pid for "start & restart"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->outputStyle = new SymfonyStyle($this->input, $this->output);

        try {
            $action = $input->getArgument('action');
            switch ($action) {
                case 'start':
                    $this->start();
                    break;
                case 'stop':
                    $this->stop();
                    break;
                case 'restart':
                    $this->restart();
                    break;
                case 'reload':
                    $this->reload();
                    break;
                case 'info':
                    $this->showInfo();
                    break;
                default:
                    $this->outputStyle->note('Usage: [/usr/local/bin/php] bin/laravels start|stop|restart|reload|info');
                    break;
            }
        } catch (\Exception $e) {
            $error = sprintf(
                'onRequest: Uncaught exception "%s"([%d]%s) at %s:%s, %s%s',
                get_class($e),
                $e->getCode(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                PHP_EOL,
                $e->getTraceAsString()
            );
            $this->outputStyle->error($error);
        }
    }

    public function start()
    {
        // Initialize configuration config/laravels.json
        $options = $this->input->getOptions();
        $options = array_filter($options);
        array_walk($options, function (&$value, $key) {
            $value = '--' . $key;
        });
        $cmd = trim('laravels config ' . implode(' ', $options));
        $this->runArtisanCommand($cmd);

        $this->showInfo();

        // Here we go...
        $config = $this->getConfig();

        if (in_array($config['svrConf']['socket_type'], [SWOOLE_SOCK_UNIX_DGRAM, SWOOLE_SOCK_UNIX_STREAM])) {
            $listenAt = $config['svrConf']['listen_ip'];
        } else {
            $listenAt = sprintf('%s:%s', $config['svrConf']['listen_ip'], $config['svrConf']['listen_port']);
        }

        if (!$config['svrConf']['ignore_check_pid'] && file_exists($config['svrConf']['swoole']['pid_file'])) {
            $pid = (int)file_get_contents($config['svrConf']['swoole']['pid_file']);
            if ($pid > 0 && self::kill($pid, 0)) {
                $this->outputStyle->warning(sprintf('Swoole[PID=%d] is already running at %s.', $pid, $listenAt));
                return 1;
            }
        }

        if ($config['svrConf']['swoole']['daemonize']) {
            $this->outputStyle->note(sprintf('Swoole is running in daemon mode, and listening at %s, see "ps -ef|grep laravels".', $listenAt));
        } else {
            $this->outputStyle->note(sprintf('Swoole is listening at %s, press Ctrl+C to quit.', $listenAt));
        }

        $lvs = new \Hhxsv5\LaravelS\LaravelS($config['svrConf'], $config['laravelConf']);
        $lvs->setOutputStyle($this->outputStyle);
        $lvs->run();

        return 0;
    }

    public function stop()
    {
        $config = $this->getConfig();
        $pidFile = $config['svrConf']['swoole']['pid_file'];
        if (!file_exists($pidFile)) {
            $this->outputStyle->warning('It seems that LaravelS is not running.');
            return 1;
        }

        $pid = file_get_contents($pidFile);
        if (self::kill($pid, 0)) {
            if (self::kill($pid, SIGTERM)) {
                // Make sure that master process quit
                $time = 1;
                $waitTime = isset($config['svrConf']['swoole']['max_wait_time']) ? $config['svrConf']['swoole']['max_wait_time'] : 60;
                $this->outputStyle->note("The max wait time to forcibly stop is {$waitTime}s.");
                while (self::kill($pid, 0)) {
                    if ($time > $waitTime) {
                        $this->outputStyle->warning("PID[{$pid}] cannot be stopped gracefully in {$waitTime}s, will be stopped forced right now.");
                        return 1;
                    }
                    $this->outputStyle->note("Waiting PID[{$pid}] to stop. [{$time}]");
                    sleep(1);
                    $time++;
                }
                if (file_exists($pidFile)) {
                    unlink($pidFile);
                }
                $this->outputStyle->note("PID[{$pid}] is stopped.");
                return 0;
            } else {
                $this->outputStyle->error("PID[{$pid}] is stopped failed.");
                return 1;
            }
        } else {
            $this->outputStyle->error("PID[{$pid}] does not exist, or permission denied.");
            return 1;
        }
    }

    public function restart()
    {
        $code = $this->stop();
        if ($code !== 0) {
            return $code;
        }
        return $this->start();
    }

    public function reload()
    {
        $config = $this->getConfig();
        $pidFile = $config['svrConf']['swoole']['pid_file'];
        if (!file_exists($pidFile)) {
            $this->outputStyle->error('It seems that Swoole is not running.');
            return;
        }

        $pid = file_get_contents($pidFile);
        if (!$pid || !self::kill($pid, 0)) {
            $this->outputStyle->error("PID[{$pid}] does not exist, or permission denied.");
            return;
        }

        if (self::kill($pid, SIGUSR1)) {
            $now = date('Y-m-d H:i:s');
            $this->outputStyle->note("PID[{$pid}] is reloaded at {$now}.");
        } else {
            $this->outputStyle->error("PID[{$pid}] is reloaded failed.");
        }
    }

    public function showInfo()
    {
        $this->runArtisanCommand('laravels info');
    }

    public function artisanCmd($subCmd = null)
    {
        $phpCmd = sprintf('%s -c "%s"', PHP_BINARY, php_ini_loaded_file());
        $artisanCmd = sprintf('%s %s/artisan', $phpCmd, $this->basePath);
        if ($subCmd !== null) {
            $artisanCmd .= ' ' . $subCmd;
        }
        return $artisanCmd;
    }

    public function runArtisanCommand($cmd)
    {
        $cmd = $this->artisanCmd($cmd);
        self::runCommand($cmd);
    }

    public function getConfig()
    {
        if (empty($this->config)) {
            $json = file_get_contents($this->basePath . '/storage/laravels.json');
            $this->config = (array)json_decode($json, true);
        }
        return $this->config;
    }

    public static function runCommand($cmd, $input = null)
    {
        $fp = popen($cmd, 'w');
        if ($fp === false) {
            return false;
        }
        if ($input !== null) {
            fwrite($fp, $input);
        }
        pclose($fp);
        return true;
    }

    public static function kill($pid, $sig)
    {
        try {
            return Process::kill($pid, $sig);
        } catch (\Exception $e) {
            return false;
        }
    }

}