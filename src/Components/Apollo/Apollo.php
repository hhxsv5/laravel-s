<?php

namespace Hhxsv5\LaravelS\Components\Apollo;

use Symfony\Component\HttpClient\CurlHttpClient;

class Apollo
{
    protected $server;
    protected $appId;
    protected $cluster    = 'default';
    protected $namespaces = ['application'];
    protected $clientIp;

    /**@var Apollo $client */
    protected $client;

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
        }

        $this->client = new CurlHttpClient([
            'base_uri'     => $this->server,
            'max_duration' => isset($settings['pull_timeout']) ? $settings['pull_timeout'] : 5,
        ]);
    }

    public static function createFromEnv()
    {
        $envs = getenv();
        if (!isset($envs['APOLLO_SERVER'], $envs['APOLLO_APP_ID'])) {
            throw new \InvalidArgumentException('Missing environment variable APOLLO_SERVER & APOLLO_APP_ID');
        }
        $options = [
            'server'       => $envs['APOLLO_SERVER'],
            'app_id'       => $envs['APOLLO_APP_ID'],
            'cluster'      => isset($envs['APOLLO_CLUSTER']) ? $envs['APOLLO_CLUSTER'] : null,
            'namespaces'   => isset($envs['APOLLO_NAMESPACES']) ? explode(',', $envs['APOLLO_NAMESPACES']) : null,
            'client_ip'    => isset($envs['APOLLO_CLIENT_IP']) ? $envs['APOLLO_CLIENT_IP'] : null,
            'pull_timeout' => isset($envs['APOLLO_PULL_TIMEOUT']) ? $envs['APOLLO_PULL_TIMEOUT'] : null,
        ];
        return new static($options);
    }


    public function pullBatch(array $namespaces, $withReleaseKey = false, array $options = [])
    {
        $uri = sprintf('%s/configs/%s/%s/', $this->server, $this->appId, $this->cluster);
        $responses = [];
        foreach ($namespaces as $namespace) {
            $responses[$namespace] = $this->client->request('GET', $uri . $namespace, [
                    'query' => [
                        'releaseKey' => $withReleaseKey && isset($this->releaseKeys[$namespace]) ? $this->releaseKeys[$namespace] : null,
                        'ip'         => $this->clientIp,
                    ],
                ] + $options);
        }

        $configs = [];
        foreach ($responses as $namespace => $response) {
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                $configs[$namespace] = json_decode((string)$response->getContent(false), true);
                $this->releaseKeys[$namespace] = $configs[$namespace]['releaseKey'];
            } elseif ($statusCode === 304) {
                // ignore 304
            }
        }
        return $configs;
    }

    public function pullAll($withReleaseKey = false, array $options = [])
    {
        return $this->pullBatch($this->namespaces, $withReleaseKey, $options);
    }

    public function pullAllAndSave($filepath, $keepOld = false, array $options = [])
    {
        $all = $this->pullAll(false, $options);
        if (count($all) !== count($this->namespaces)) {
            throw new \RuntimeException('Incomplete Apollo configuration list');
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
        if ($keepOld && file_exists($filepath)) {
            rename($filepath, $filepath . '.' . date('YmdHis'));
        }
        file_put_contents($filepath, implode(PHP_EOL, $configs));
        return $configs;
    }

    public function startWatchNotification(callable $callback, array $options = [])
    {
        if (!isset($options['max_duration']) || $options['max_duration'] < 60) {
            $options['max_duration'] = 70;
        }
        if (!isset($options['timeout']) || $options['timeout'] < 60) {
            $options['timeout'] = 70;
        }
        $this->watching = true;
        $this->notifications = [];
        foreach ($this->namespaces as $namespace) {
            $this->notifications[$namespace] = ['namespaceName' => $namespace, 'notificationId' => -1];
        }
        while ($this->watching) {
            $response = $this->client->request('GET', '/notifications/v2', [
                    'query' => [
                        'appId'         => $this->appId,
                        'cluster'       => $this->cluster,
                        'notifications' => json_encode(array_values($this->notifications)),
                    ],
                ] + $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                $notifications = json_decode((string)$response->getContent(false), true);
                if (empty($notifications)) {
                    continue;
                }
                array_walk($notifications, function (&$notification) {
                    unset($notification['messages']);
                });
                $this->notifications = array_merge($this->notifications, array_column($notifications, null, 'namespaceName'));
                $callback($notifications);
            } elseif ($statusCode === 304) {
                // ignore 304
            }
        }
    }

    public function stopWatchNotification()
    {
        $this->watching = false;
    }
}