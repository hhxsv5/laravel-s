<?php

namespace Hhxsv5\LaravelS\Swoole\Timer;

abstract class CronJob implements CronJobInterface
{
    /**
     * Swoole timer id
     * @var int
     */
    protected $timerId;

    /**
     * The interval of Job in millisecond
     * @var int
     */
    protected $interval;

    /**
     * Whether run immediately after start
     * @var bool
     */
    protected $isImmediate;

    /**
     * CronJob constructor.
     * Optional:
     *     argument 1 is interval, int ms, default null, overridden by method interval()
     *     argument 2 is isImmediate, bool, default false, overridden by method isImmediate()
     */
    public function __construct()
    {
        $args = func_get_args();
        $config = isset($args[0]) ? $args[0] : [];
        if (is_array($config)) {
            if (array_key_exists(0, $config)) {
                $this->interval = $config[0];
            }
            if (array_key_exists(1, $config)) {
                $this->isImmediate = $config[1];
            }
        }
    }

    /**
     * @return int
     */
    public function interval()
    {
        return $this->interval;
    }

    /**
     * @return bool $isImmediate
     */
    public function isImmediate()
    {
        return $this->isImmediate;
    }

    public function setTimerId($timerId)
    {
        $this->timerId = $timerId;
    }

    public function stop()
    {
        if (!empty($this->timerId)) {
            \swoole_timer_clear($this->timerId);
        }
    }

}