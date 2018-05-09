<?php

class Cache extends Genome {

    protected static $cache = [];

    public static function set($from, $fn = null, $id = null) {
        $n = self::__($from);
        $t = $id ? (string) $id : (file_exists($from) ? filemtime($from) : 0);
        if ($t || $fn) {
            $x = File::open($n)->import([-1]);
            if ((is_string($t) && $t !== $x[0]) || $t > $x[0]) {
                $content = null;
                if (is_callable($fn)) {
                    File::export([$t, $content = call_user_func($fn, $from, $t, $x)])->saveTo($n, 0600);
                } else if (is_file($from)) {
                    $content = require $from;
                }
                return $content;
            }
        }
        return false;
    }

    public static function get($from, $fail = false) {
        return File::open(self::__($from))->import([0, $fail])[1];
    }

    public static function reset($from = null) {
        if (isset($from)) {
            File::open(self::__($from))->delete();
        } else {
            foreach (self::$cache as $k => $v) {
                File::open($k)->delete();
            }
        }
        return true;
    }

    public static function expire($from, $id = null) {
        $n = self::__($from);
        if (!file_exists($n)) {
            return true;
        }
        $t = $id ? (string) $id : (file_exists($from) ? filemtime($from) : 0);
        $x = File::open($n)->import([-1]);
        return is_string($t) && $t !== $x[0] || $t > $x[0];
    }

    public static function ID($from, $fail = -1) {
        return File::open(self::__($from))->import([$fail])[0];
    }

    private static function __($s) {
        if (is_dir($s) || !file_exists($s)) {
            $s .= '.cache';
            File::set("")->saveTo($s, 0600);
        }
        $f = str_replace(ROOT, CACHE, $s) . '.php';
        self::$cache[$f] = file_exists($f) ? filemtime($f) : 0;
        return $f;
    }

}