<?php

final class Cache extends Genome {

    protected static function f($id) {
        $root = constant(u(static::class)) . DS;
        if (is_file($id)) {
            return $root . Path::R($id, LOT, DS) . '.php';
        }
        $id = dechex(crc32($id)); // Convert string to a safe file name
        return $root . $id . '.php';
    }

    protected static function t($in, $t = null) {
        $t = $t ?? time();
        return is_string($in) ? strtotime($in, $t) - $t : $in;
    }

    public static function alt(string $id, callable $fn, $for = '1 day') {
        return self::expire($id, $for) ? self::set($id, $fn, [$id, self::f($id)])[0] : self::get($id);
    }

    public static function expire(string $id, $for = '1 day') {
        if (is_file($f = self::f($id))) {
            $t = time();
            return $t - self::t($for, $t) > filemtime($f);
        }
        return true;
    }

    public static function get(string $id) {
        return File::open(self::f($id))->import(null);
    }

    public static function hit($file, callable $fn) {
        if (is_array($file)) {
            $i = 0;
            $files = [];
            foreach ($file as $v) {
                if (is_file($v)) {
                    $files[] = $v;
                    if ($i < ($t = filemtime($v))) {
                        $i = $t;
                    }
                }
            }
            $f = self::f($id = json_encode($files));
            return !is_file($f) || $i > filemtime($f) ? self::set($id, $fn, [$files, $f])[0] : self::get($id);
        }
        $f = self::f($file);
        return !is_file($f) || filemtime($file) > filemtime($f) ? self::set($file, $fn, [$file, $f])[0] : self::get($file);
    }

    public static function reset($id = null): array {
        $out = [];
        if (is_array($id)) {
            foreach ($id as $v) {
                $out[] = File::open(self::f($v))->delete();
            }
            return $out;
        } else if (isset($id)) {
            return File::open(self::f($id))->delete();
        }
        foreach (g(constant(u(static::class))) as $v) {
            $out = concat($out, File::open($v)->delete());
        }
        return $out;
    }

    public static function set(string $id, callable $fn, array $lot = []): array {
        File::export($r = call_user_func($fn, ...$lot))->saveTo($f = self::f($id), 0600);
        return [$r, $f, filemtime($f)];
    }

}