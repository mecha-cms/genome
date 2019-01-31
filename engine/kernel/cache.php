<?php

class Cache extends Genome {

    protected static $cache = [];

    public static function set(string $id, callable $fn, int $touch = null) {
        File::export($r = call_user_func($fn))->saveTo($f = self::f($id), 0600);
        if ($touch) touch($f, $touch);
        self::$cache[$f] = filemtime($f);
        return $r;
    }

    public static function get(string $id, $fail = false) {
        return File::open(self::f($id))->import($fail);
    }

    public static function reset(string $id) {
        return File::open(self::f($id))->delete();
    }

    public static function expire(string $id, $at = '1 day') {
        if (!is_file($f = self::f($id))) {
            return true;
        }
        $a = self::$cache[$f] ?? filemtime($f);
        $b = $_SERVER['REQUEST_TIME'] ?? time();
        return !($b + self::at($at, $b) < $a);
    }

    public static function of(string $id, callable $fn, $at = '1 day', $fail = false) {
        return self::expire($id, $at) ? self::set($id, $fn, self::at($at)) : self::get($id, $fail);
    }

    protected static function at($in, $b = null) {
        $b = $_SERVER['REQUEST_TIME'] ?? time();
        return is_string($in) ? strtotime($in, $b) - $b : $in;
    }

    protected static function f($id) {
        $root = constant(u(static::class)) . DS;
        if (is_file($id)) {
            return $root . Path::R($id, LOT, DS) . '.php';
        }
        $id = dechex(crc32($id)); // Convert string to a safe file name
        return $root . $id . '.php';
    }

}