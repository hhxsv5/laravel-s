<?php

namespace Hhxsv5\LaravelS\Tests;

use Hhxsv5\LaravelS\Components\HttpClient\SimpleHttpTrait;

class SimpleHttpTest extends TestCase
{
    use SimpleHttpTrait;

    public function testHttpGet()
    {
        $response = $this->httpGet('http://httpbin.org/get', ['timeout' => 3]);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('body', $response);
        $body = $response['body'];
        $json = json_decode($body, true);
        $this->assertIsArray($json);
    }
}