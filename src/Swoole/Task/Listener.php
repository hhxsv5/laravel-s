<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

abstract class Listener
{
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * The logic of handling event
     * @return void
     */
    abstract public function handle();
}