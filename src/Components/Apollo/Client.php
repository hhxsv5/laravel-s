<?php

namespace Hhxsv5\LaravelS\Components\Apollo;

use Hhxsv5\LaravelS\Components\HttpClient\SimpleHttpTrait;
use Hhxsv5\LaravelS\Swoole\Coroutine\Context;
use Swoole\Coroutine;

class Client
{
    use SimpleHttpTrait;

    protected $server;
    protected $appId;
    protected $cluster      = 'default';
    protected $namespaces   = ['application'];
    protected $clientIp;
    protected $pullTimeout  = 5;
    protected $backupOldEnv = false;

    protected $releaseKeys   = [];
    protected $notifications = [];

    protected $watching = true;

    public function __construct(array $settings)
    {
        $this->server = $settings['server'];
        $this->appId = $settings['app_id'];
        if (isset($settings['cluster'])) {
            $this->cluster = $settings['cluster'];
        }
        if (isset($settings['namespaces'])) {
            $this->namespaces = $settings['namespaces'];
        }
        if (isset($settings['client_ip'])) {
            $this->clientIp = $settings['client_ip'];
        } else {
            $this->clientIp = current(swoole_get_local_ip()) ?: null;
        }
        if (isset($settings['pull_timeout'])) {
            $this->pullTimeout = (int)$settings['pull_timeout'];
        }
        if (isset($settings['backup_old_env'])) {
            $this->backupOldEnv = (bool)$settings['backup_old_env'];
        }
    }

    public static function createFromEnv()
    {
        if (!getenv('APOLLO_SERVER') || !getenv('APOLLO_APP_ID')) {
            throw new \InvalidArgumentException('Missing environment variable APOLLO_SERVER & APOLLO_APP_ID');
        }
        $settings = [
            'server'         => getenv('APOLLO_SERVER'),
            'app_id'         => getenv('APOLLO_APP_ID'),
            'cluster'        => getenv('APOLLO_CLUSTER') ?: null,
            'namespaces'     => explode(',', getenv('APOLLO_NAMESPACES')) ?: null,
            'client_ip'      => getenv('APOLLO_CLIENT_IP') ?: null,
            'pull_timeout'   => getenv('APOLLO_PULL_TIMEOUT') ?: null,
            'backup_old_env' => getenv('APOLLO_BACKUP_OLD_ENV') ?: false,
        ];
        return new static($settings);
    }


    public function pullBatch(array $namespaces, $withReleaseKey = false, array $options = [])
    {
        $configs = [];
        $uri = sprintf('%s/configs/%s/%s/', $this->server, $this->appId, $this->cluster);
        foreach ($namespaces as $namespace) {
            $url = $uri . $namespace . '?' . http_build_query([
                    'releaseKey' => $withReleaseKey && isset($this->releaseKeys[$namespace]) ? $this->releaseKeys[$namespace] : null,
                    'ip'         => $this->clientIp,
                ]);
            $timeout = isset($options['timeout']) ? $options['timeout'] : $this->pullTimeout;
            $response = $this->httpGet($url, compact('timeout'));
            if ($response['statusCode'] === 200) {
                $configs[$namespace] = json_decode($response['body'], true);
                $this->releaseKeys[$namespace] = $configs[$namespace]['releaseKey'];
            } elseif ($response['statusCode'] === 304) {
                // ignore 304
            }

        }
        return $configs;
    }

    public function pullAll($withReleaseKey = false, array $options = [])
    {
        return $this->pullBatch($this->namespaces, $withReleaseKey, $options);
    }

    public function pullAllAndSave($filepath, array $options = [])
    {
        $all = $this->pullAll(false, $options);
        if (count($all) !== count($this->namespaces)) {
            $lackNamespaces = array_diff($this->namespaces, array_keys($all));
            throw new \RuntimeException('Missing Apollo configurations for namespaces ' . implode(',', $lackNamespaces));
        }
        $configs = [];
        foreach ($all as $namespace => $config) {
            $configs[] = '# Namespace: ' . $config['namespaceName'];
            ksort($config['configurations']);
            foreach ($config['configurations'] as $key => $value) {
                $configs[] = sprintf('%s=%s', $key, $value);
            }
        }
        if (empty($configs)) {
            throw new \RuntimeException('Empty Apollo configuration list');
        }
        if ($this->backupOldEnv && file_exists($filepath)) {
            rename($filepath, $filepath . '.' . date('YmdHis'));
        }
        $fileContent = implode(PHP_EOL, $configs);
        if (Context::inCoroutine()) {
            Coroutine::writeFile($filepath, $fileContent);
        } else {
            file_put_contents($filepath, $fileContent);
        }
        return $configs;
    }

    public function startWatchNotification(callable $callback, array $options = [])
    {
        if (!isset($options['timeout']) || $options['timeout'] < 60) {
            $options['timeout'] = 70;
        }
        $this->watching = true;
        $this->notifications = [];
        foreach ($this->namespaces as $namespace) {
            $this->notifications[$namespace] = ['namespaceName' => $namespace, 'notificationId' => -1];
        }
        while ($this->watching) {
            $url = sprintf('%s/notifications/v2?%s',
                $this->server,
                http_build_query([
                    'appId'         => $this->appId,
                    'cluster'       => $this->cluster,
                    'notifications' => json_encode(array_values($this->notifications)),
                ])
            );
            $response = $this->httpGet($url, $options);

            if ($response['statusCode'] === 200) {
                $notifications = json_decode($response['body'], true);
                if (empty($notifications)) {
                    continue;
                }
                if (!empty($this->notifications) && current($this->notifications)['notificationId'] !== -1) { // Ignore the first pull
                    $callback($notifications);
                }
                array_walk($notifications, function (&$notification) {
                    unset($notification['messages']);
                });
                $this->notifications = array_merge($this->notifications, array_column($notifications, null, 'namespaceName'));
            } elseif ($response['statusCode'] === 304) {
                // ignore 304
            }
        }
    }

    public function stopWatchNotification()
    {
        $this->watching = false;
    }
}
