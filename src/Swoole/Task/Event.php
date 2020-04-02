<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;

abstract class Event extends BaseTask
{
    use SerializesModels;

    /**
     * The listeners for this event.
     * @return array
     */
    protected $listeners = [];

    /**
     * Get the listener list for this event.
     * @return array
     */
    public function getListeners()
    {
        return $this->listeners;
    }

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