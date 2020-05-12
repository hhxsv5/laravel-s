<?php

namespace Hhxsv5\LaravelS\Swoole\Coroutine;

use Swoole\Coroutine;

class Context
{
    protected static $box = [];

    public static function get($key)
    {
        $cid = Coroutine::getCid();
        if ($cid < 0) {
            return null;
        }
        if (isset(self::$box[$cid][$key])) {
            return self::$box[$cid][$key];
        }
        return null;
    }

    public static function put($key, $item)
    {
        $cid = Coroutine::getCid();
        if ($cid > 0) {
            self::$box[$cid][$key] = $item;
        }
    }

    public static function delete($key = null)
    {
        $cid = Coroutine::getCid();
        if ($cid > 0) {
            if ($key) {
                unset(self::$box[$cid][$key]);
            } else {
                unset(self::$box[$cid]);
            }
        }
    }

    public static function inCoroutine()
    {
        return class_exists('Swoole\Coroutine', false) && Coroutine::getCid() > 0;
    }
}