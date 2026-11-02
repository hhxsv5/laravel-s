<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Swoole;

use Hhxsv5\LaravelS\Swoole\DynamicResponse;
use Hhxsv5\LaravelS\Tests\TestCase;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DynamicResponseTest extends TestCase
{
    /** @dataProvider smallBodies */
    public function testSendsSmallContentWithoutChunking(string $content): void
    {
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->never())->method('write');
        $target->expects($this->once())->method('end')->with($content)->willReturn(true);

        $response = new DynamicResponse($target, new Response($content));
        $response->setChunkLimit(10);
        $response->sendContent();
    }

    public static function smallBodies(): array
    {
        return ['normal' => ['hello'], 'zero' => ['0'], 'exact chunk limit' => ['0123456789']];
    }

    public function testEndsEmptyResponseWithoutWriting(): void
    {
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->never())->method('write');
        $target->expects($this->once())->method('end')->with()->willReturn(true);

        (new DynamicResponse($target, new Response('')))->sendContent();
    }

    public function testChunksLargeResponseWithoutLosingBytes(): void
    {
        $content = '你好，LaravelS! ' . str_repeat('abcdef', 5);
        $chunks = [];
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->atLeast(2))->method('write')->willReturnCallback(function ($chunk) use (&$chunks) {
            $chunks[] = $chunk;
            return true;
        });
        $target->expects($this->once())->method('end')->with()->willReturn(true);

        $response = new DynamicResponse($target, new Response($content));
        $response->setChunkLimit(10);
        $response->sendContent();

        $this->assertSame($content, implode('', $chunks));
        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(10, strlen($chunk));
        }
    }

    public function testCapturesStreamedContentAndRestoresOutputBuffer(): void
    {
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->once())->method('end')->with('firstsecond')->willReturn(true);
        $source = new StreamedResponse(function () {
            echo 'first';
            echo 'second';
        });
        $level = ob_get_level();

        (new DynamicResponse($target, $source))->sendContent();

        $this->assertSame($level, ob_get_level());
    }
}
