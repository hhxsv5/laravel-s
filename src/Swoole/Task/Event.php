<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;

abstract class Event extends BaseTask
{
    use SerializesModels;

    /**
     * Trigger an event
     * @param Event $event
     * @return bool
     */
    public static function fire(self $event)
    {
        return $event->task($event);
    }
}