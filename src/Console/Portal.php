<?php

namespace Hhxsv5\LaravelS\Console;

use Hhxsv5\LaravelS\LaravelS;
use Hhxsv5\LaravelS\Swoole\Traits\LogTrait;
use Swoole\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Portal extends Command
{
    use LogTrait;

    protected $basePath;

    /**
     * @var InputInterface $input
     */
    protected $input;

    /**
     * @var OutputInterface $output
     */
    protected $output;

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
        $this->addOption('env', 'e', InputOption::VALUE_OPTIONAL, 'The environment the command should run under, this feature requires Laravel 5.2+');
        $this->addOption('daemonize', 'd', InputOption::VALUE_NONE, 'Whether run as a daemon for "start & restart"');
        $this->addOption('ignore', 'i', InputOption::VALUE_NONE, 'Whether ignore checking process pid for "start & restart"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        LaravelS::setOutputStyle(new SymfonyStyle($this->input, $this->output));

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
                    $help = <<<EOS

Usage: 
  [%s] ./bin/laravels [options] <action>

Arguments:
  action                start|stop|restart|reload|info|help

Options:
  -e, --env             The environment the command should run under, this feature requires Laravel 5.2+
  -d, --daemonize       Whether run as a daemon for "start & restart"
  -i, --ignore          Whether ignore checking process pid for "start & restart"
EOS;

                    $this->info(sprintf($help, PHP_BINARY));
                    break;
            }
        } catch (\Exception $e) {
            $error = sprintf(
                'Uncaught exception "%s"([%d]%s) at %s:%s, %s%s',
                get_class($e),
                $e->getCode(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                PHP_EOL,
                $e->getTraceAsString()
            );
            $this->error($error);
        }
    }

    public function start()
    {
        // Initialize configuration config/laravels.json
        $options = $this->input->getOptions();
        unset($options['env']);
        $options = array_filter($options);
        $optionStr = '';
        foreach ($options as $key => $value) {
            $optionStr .= sprintf('--%s%s ', $key, is_bool($value) ? '' : ('=' . $value));
        }
        $cmd = trim('laravels config ' . $optionStr);
        $this->runArtisanCommand($cmd);

        $this->showInfo();

        // Here we go...
        $config = $this->getConfig();

        if (in_array($config['server']['socket_type'], [SWOOLE_SOCK_UNIX_DGRAM, SWOOLE_SOCK_UNIX_STREAM])) {
            $listenAt = $config['server']['listen_ip'];
        } else {
            $listenAt = sprintf('%s:%s', $config['server']['listen_ip'], $config['server']['listen_port']);
        }

        if (!$config['server']['ignore_check_pid'] && file_exists($config['server']['swoole']['pid_file'])) {
            $pid = (int)file_get_contents($config['server']['swoole']['pid_file']);
            if ($pid > 0 && self::kill($pid, 0)) {
                $this->warning(sprintf('Swoole[PID=%d] is already running at %s.', $pid, $listenAt));
                return 1;
            }
        }

        if ($config['server']['swoole']['daemonize']) {
            $this->info(sprintf('Swoole is running in daemon mode, and listening at %s, see "ps -ef|grep laravels".', $listenAt));
        } else {
            $this->info(sprintf('Swoole is listening at %s, press Ctrl+C to quit.', $listenAt));
        }

        (new LaravelS($config['server'], $config['laravel']))->run();

        return 0;
    }

    public function stop()
    {
        $config = $this->getConfig();
        $pidFile = $config['server']['swoole']['pid_file'];
        if (!file_exists($pidFile)) {
            $this->warning('It seems that Swoole is not running.');
            return 0;
        }

        $pid = file_get_contents($pidFile);
        if (self::kill($pid, 0)) {
            if (self::kill($pid, SIGTERM)) {
                // Make sure that master process quit
                $time = 1;
                $waitTime = isset($config['server']['swoole']['max_wait_time']) ? $config['server']['swoole']['max_wait_time'] : 60;
                $this->info("The max time of waiting to forcibly stop is {$waitTime}s.");
                while (self::kill($pid, 0)) {
                    if ($time > $waitTime) {
                        $this->warning("Swoole [PID={$pid}] cannot be stopped gracefully in {$waitTime}s, will be stopped forced right now.");
                        return 1;
                    }
                    $this->info("Waiting Swoole[PID={$pid}] to stop. [{$time}]");
                    sleep(1);
                    $time++;
                }
                if (file_exists($pidFile)) {
                    unlink($pidFile);
                }
                $this->info("Swoole [PID={$pid}] is stopped.");
                return 0;
            } else {
                $this->error("Swoole [PID={$pid}] is stopped failed.");
                return 1;
            }
        } else {
            $this->warning("Swoole [PID={$pid}] does not exist, or permission denied.");
            return 0;
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
        $pidFile = $config['server']['swoole']['pid_file'];
        if (!file_exists($pidFile)) {
            $this->error('It seems that Swoole is not running.');
            return;
        }

        $pid = file_get_contents($pidFile);
        if (!$pid || !self::kill($pid, 0)) {
            $this->error("Swoole [PID={$pid}] does not exist, or permission denied.");
            return;
        }

        if (self::kill($pid, SIGUSR1)) {
            $now = date('Y-m-d H:i:s');
            $this->info("Swoole [PID={$pid}] is reloaded at {$now}.");
        } else {
            $this->error("Swoole [PID={$pid}] is reloaded failed.");
        }
    }

    public function showInfo()
    {
        $this->runArtisanCommand('laravels info');
    }

    public function artisanCmd($subCmd)
    {
        $phpCmd = sprintf('%s -c "%s"', PHP_BINARY, php_ini_loaded_file());
        $env = $this->input->getOption('env');
        $envs = $env ? "APP_ENV={$env}" : '';
        $artisanCmd = trim(sprintf('%s %s %s/artisan %s', $envs, $phpCmd, $this->basePath, $subCmd));
        return $artisanCmd;
    }

    public function runArtisanCommand($cmd)
    {
        $cmd = $this->artisanCmd($cmd);
        self::runCommand($cmd);
    }

    public function getConfig()
    {
        $json = file_get_contents($this->basePath . '/storage/laravels.json');
        return (array)json_decode($json, true);
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
