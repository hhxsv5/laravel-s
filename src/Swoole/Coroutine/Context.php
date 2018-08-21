<?php

namespace Hhxsv5\LaravelS\Swoole\Coroutine;

use Swoole\Coroutine;

class Context
{
    protected static $box = [];

    public static function get($key)
    {
        $uid = Coroutine::getuid();
        if ($uid < 0) {
            return null;
        }
        if (isset(self::$box[$uid][$key])) {
            return self::$box[$uid][$key];
        }
        return null;
    }

    public static function put($key, $item)
    {
        $uid = Coroutine::getuid();
        if ($uid > 0) {
            self::$box[$uid][$key] = $item;
        }
    }

    public static function delete($key = null)
    {
        $uid = Coroutine::getuid();
        if ($uid > 0) {
            if ($key) {
                unset(self::$box[$uid][$key]);
            } else {
                unset(self::$box[$uid]);
            }
        }
    }
}