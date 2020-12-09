<?php

namespace Hhxsv5\LaravelS\Swoole\Timer;

use Swoole\Timer;

abstract class CronJob implements CronJobInterface
{
    /**
     * The seconds of global timer locking
     * @var int
     */
    const GLOBAL_TIMER_LOCK_SECONDS = 60;

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
     * The lock key of global timer
     * @var string
     */
    protected static $globalTimerLockKey;

    /**
     * Whether enable CronJob
     * @var bool
     */
    protected static $enable = true;

    /**
     * CronJob constructor.
     * Optional:
     *     argument 0 is interval, int ms, default null, overridden by method interval()
     *     argument 1 is isImmediate, bool, default false, overridden by method isImmediate()
     */
    public function __construct()
    {
        $args = func_get_args();
        $config = isset($args[0]) ? $args[0] : [];
        if (is_array($config)) {
            if (isset($config[0])) {
                $this->interval = $config[0];
            }
            if (isset($config[1])) {
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
     * @return bool
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
        if ($this->timerId && Timer::exists($this->timerId)) {
            Timer::clear($this->timerId);
        }
    }

    public static function getGlobalTimerCacheKey()
    {
        return 'laravels:timer:' . strtolower(self::$globalTimerLockKey);
    }

    public static function getGlobalTimerLock()
    {
        /**@var \Illuminate\Redis\Connections\PhpRedisConnection $redis */
        $redis = app('redis');

        $key = self::getGlobalTimerCacheKey();
        $value = self::getCurrentInstanceId();
        $expire = self::GLOBAL_TIMER_LOCK_SECONDS;
        $result = $redis->set($key, $value, 'ex', $expire, 'nx');
        // Compatible with Predis and PhpRedis
        return $result === true || ((string)$result === 'OK');
    }

    protected static function getCurrentInstanceId()
    {
        return sprintf('%s:%d', current(swoole_get_local_ip()) ?: config('laravels.listen_ip'), config('laravels.listen_port'));
    }

    public static function isGlobalTimerAlive()
    {
        /**@var \Illuminate\Redis\Connections\PhpRedisConnection $redis */
        $redis = app('redis');
        return (bool)$redis->exists(self::getGlobalTimerCacheKey());
    }

    public static function isCurrentTimerAlive()
    {
        /**@var \Illuminate\Redis\Connections\PhpRedisConnection $redis */
        $redis = app('redis');
        $key = self::getGlobalTimerCacheKey();
        $instanceId = $redis->get($key);
        return $instanceId === self::getCurrentInstanceId();
    }

    public static function renewGlobalTimerLock($expire)
    {
        /**@var \Illuminate\Redis\Connections\PhpRedisConnection $redis */
        $redis = app('redis');
        return (bool)$redis->expire(self::getGlobalTimerCacheKey(), $expire);
    }

    public static function setGlobalTimerLockKey($lockKey)
    {
        self::$globalTimerLockKey = $lockKey;
    }

    public static function checkSetEnable()
    {
        if (self::isGlobalTimerAlive()) {
            // Reset current timer to avoid repeated execution
            self::setEnable(self::isCurrentTimerAlive());
        } else {
            // Compete for timer lock
            self::setEnable(self::getGlobalTimerLock());
        }
    }

    public static function setEnable($enable)
    {
        self::$enable = (bool)$enable;
    }

    public static function isEnable()
    {
        return self::$enable;
    }
}