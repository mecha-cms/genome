<?php

class Cache extends Genome {

    protected static $cache = [];

    public static function set(string $id, callable $fn, int $touch = null /* @internal */) {
        File::export($r = call_user_func($fn))->saveTo($f = self::f($id), 0600);
        $touch && touch($f, $touch);
        return [$r, $f, self::$cache[$f] = filemtime($f)];
    }

    public static function get(string $id) {
        return File::open(self::f($id))->import(null);
    }

    public static function reset(string $id = null) {
        if (!isset($id)) {
            $out = [];
            foreach (glob(constant(u(static::class)) . DS . '*', GLOB_NOSORT) as $v) {
                $out = concat($out, File::open($v)->delete());
            }
            return $out;
        }
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

    public static function of(string $id, callable $fn, $at = '1 day') {
        return self::expire($id, $at) ? self::set($id, $fn, self::at($at))[0] : self::get($id);
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