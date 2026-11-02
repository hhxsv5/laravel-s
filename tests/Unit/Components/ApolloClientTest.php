<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Components;

use Hhxsv5\LaravelS\Components\Apollo\Client;
use Hhxsv5\LaravelS\Tests\TestCase;

class ApolloClientTest extends TestCase
{
    private function client(): ApolloTestClient
    {
        return new ApolloTestClient([
            'server' => 'http://apollo.test', 'app_id' => 'my-app',
            'cluster' => 'prod', 'namespaces' => ['application', 'extra'],
            'client_ip' => '192.0.2.1', 'pull_timeout' => 8,
        ]);
    }

    public function testPullTracksReleaseKeysPerNamespaceAndIgnoresUnchangedResponses(): void
    {
        $client = $this->client();
        $client->responses = [
            ['statusCode' => 200, 'body' => json_encode(['releaseKey' => 'v1', 'configurations' => ['A' => '1']])],
            ['statusCode' => 304, 'body' => ''],
            ['statusCode' => 304, 'body' => ''],
            ['statusCode' => 200, 'body' => json_encode(['releaseKey' => 'v2', 'configurations' => ['B' => '2']])],
        ];

        $first = $client->pullAll();
        $second = $client->pullAll(true, ['timeout' => 2]);

        $this->assertSame(['application'], array_keys($first));
        $this->assertSame(['extra'], array_keys($second));
        $this->assertSame('v1', $first['application']['releaseKey']);
        $this->assertSame('v2', $second['extra']['releaseKey']);
        $this->assertSame(['http://apollo.test/configs/my-app/prod/application?ip=192.0.2.1', ['timeout' => 8]], $client->requests[0]);
        $this->assertSame(['http://apollo.test/configs/my-app/prod/application?releaseKey=v1&ip=192.0.2.1', ['timeout' => 2]], $client->requests[2]);
        $this->assertSame('http://apollo.test/configs/my-app/prod/extra?ip=192.0.2.1', $client->requests[3][0]);
    }

    public function testInvalidJsonAndHttpErrorsAreNotReturnedAsConfigurations(): void
    {
        $client = $this->client();
        $client->responses = [
            ['statusCode' => 200, 'body' => 'invalid json'],
            ['statusCode' => 500, 'body' => '{}'],
        ];
        $this->assertSame([], $client->pullAll());
    }

    public function testSaveSortsAndSanitizesKeys(): void
    {
        $client = $this->client();
        $client->responses = [
            ['statusCode' => 200, 'body' => json_encode(['releaseKey' => '1', 'namespaceName' => 'application', 'configurations' => ['Z-key' => 'last', 'A' => 'first']])],
            ['statusCode' => 200, 'body' => json_encode(['releaseKey' => '2', 'namespaceName' => 'extra', 'configurations' => ['X.Y' => 'value']])],
        ];
        $path = tempnam(sys_get_temp_dir(), 'apollo-test-');
        try {
            $expected = ['# Namespace: application', 'A=first', 'Z_key=last', '# Namespace: extra', 'X.Y=value'];
            $this->assertSame($expected, $client->pullAllAndSave($path));
            $this->assertSame(implode(PHP_EOL, $expected), file_get_contents($path));
        } finally {
            unlink($path);
        }
    }

    public function testMissingNamespaceDoesNotOverwriteExistingFile(): void
    {
        $client = $this->client();
        $client->responses = [
            ['statusCode' => 200, 'body' => json_encode(['releaseKey' => '1', 'namespaceName' => 'application', 'configurations' => []])],
            ['statusCode' => 500, 'body' => ''],
        ];
        $path = tempnam(sys_get_temp_dir(), 'apollo-test-');
        file_put_contents($path, 'existing');
        try {
            try {
                $client->pullAllAndSave($path);
                $this->fail('Expected missing namespace exception');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('extra', $exception->getMessage());
                $this->assertSame('existing', file_get_contents($path));
            }
        } finally {
            unlink($path);
        }
    }
}

class ApolloTestClient extends Client
{
    public $responses = [];
    public $requests = [];

    public function httpGet($url, array $options)
    {
        $this->requests[] = [$url, $options];
        return array_shift($this->responses);
    }
}
