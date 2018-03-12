<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

abstract class Listener
{
    abstract public function handle(Event $event);
}