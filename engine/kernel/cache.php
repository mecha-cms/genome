<?php

class Cache extends Genome {

    public static function set(string $id, callable $fn, array $lot = []): array {
        File::export($r = call_user_func($fn, ...$lot))->saveTo($f = self::f($id), 0600);
        return [$r, $f, filemtime($f)];
    }

    public static function get(string $id) {
        return File::open(self::f($id))->import(null);
    }

    public static function reset(string $id = null): array {
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
        $t = time();
        return $t - self::at($at, $t) > filemtime($f);
    }

    public static function alt(string $id, callable $fn, $at = '1 day') {
        return self::expire($id, $at) ? self::set($id, $fn, [$id, self::f($id)])[0] : self::get($id);
    }

    public static function hit(string $file, callable $fn) {
        if (!is_file($file)) {
            return self::set($file, $fn)[0];
        }
        $f = self::f($file);
        return filemtime($file) > filemtime($f) ? self::set($file, $fn, [$file, $f])[0] : self::get($file);
    }

    protected static function at($in, $t = null) {
        $t = $t ?? time();
        return is_string($in) ? strtotime($in, $t) - $t : $in;
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