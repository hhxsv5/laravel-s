<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

abstract class Listener
{
    /**
     * The logic of handling event
     * @param Event $event
     * @return mixed
     */
    abstract public function handle(Event $event);
}