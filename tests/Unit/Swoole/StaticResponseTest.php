<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Swoole;

use Hhxsv5\LaravelS\Swoole\StaticResponse;
use Hhxsv5\LaravelS\Tests\TestCase;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StaticResponseTest extends TestCase
{
    private $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = tempnam(sys_get_temp_dir(), 'laravels-download-');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
        parent::tearDown();
    }

    private function fileResponse(int $status = 200): BinaryFileResponse
    {
        return new BinaryFileResponse($this->path, $status, ['Content-Type' => 'text/plain']);
    }

    public function testUsesSendfileAndPreservesFileByDefault(): void
    {
        file_put_contents($this->path, 'download');
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->once())->method('sendfile')->with($this->path)->willReturn(true);
        $target->expects($this->never())->method('write');
        $target->expects($this->never())->method('end');

        (new StaticResponse($target, $this->fileResponse()))->sendContent();

        $this->assertFileExists($this->path);
    }

    public function testEndsEmptyFileWithoutCallingSendfile(): void
    {
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->once())->method('end')->with()->willReturn(true);
        $target->expects($this->never())->method('sendfile');

        (new StaticResponse($target, $this->fileResponse()))->sendContent();
    }

    public function testNotModifiedResponseDoesNotSendFile(): void
    {
        file_put_contents($this->path, 'download');
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->once())->method('end')->with()->willReturn(true);
        $target->expects($this->never())->method('sendfile');
        $target->expects($this->never())->method('write');

        (new StaticResponse($target, $this->fileResponse(304)))->sendContent();
    }

    public function testWritesAllChunksBeforeDeletingFile(): void
    {
        $content = str_repeat('download-', 4);
        file_put_contents($this->path, $content);
        $source = $this->fileResponse()->deleteFileAfterSend(true);
        $chunks = [];
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->never())->method('sendfile');
        $target->expects($this->atLeast(2))->method('write')->willReturnCallback(function ($chunk) use (&$chunks) {
            $this->assertFileExists($this->path);
            $chunks[] = $chunk;
            return true;
        });
        $target->expects($this->once())->method('end')->willReturn(true);
        $response = new StaticResponse($target, $source);
        $response->setChunkLimit(10);

        $response->sendContent();

        $this->assertSame($content, implode('', $chunks));
        $this->assertFileDoesNotExist($this->path);
    }
}
