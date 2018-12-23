<?php

class Cache extends Genome {

    protected static $cache = [];

    public static function set(string $from, $fn = null, $id = null) {
        $n = self::x($from);
        $t = $id ? (string) $id : (file_exists($from) ? filemtime($from) : 0);
        if ($t || $fn) {
            $x = File::open($n)->import([-1]);
            if ((is_string($t) && $t !== $x[0]) || $t > $x[0]) {
                $content = null;
                if (is_callable($fn)) {
                    File::export([$t, $content = call_user_func($fn, $from, $t, $x)])->saveTo($n, 0600);
                } else if (Is::file($from)) {
                    $content = require $from;
                }
                return $content;
            }
        }
        return false;
    }

    public static function get(string $from, $fail = false) {
        return File::open(self::x($from))->import([0, $fail])[1];
    }

    public static function reset(string $from = null) {
        if (isset($from)) {
            File::open(self::x($from))->delete();
        } else {
            foreach (self::$cache as $k => $v) {
                File::open($k)->delete();
            }
        }
        return true;
    }

    public static function expire(string $from, $id = null) {
        $n = self::x($from);
        if (!file_exists($n)) {
            return true;
        }
        $t = $id ? (string) $id : (file_exists($from) ? filemtime($from) : 0);
        $x = File::open($n)->import([-1]);
        return is_string($t) && $t !== $x[0] || $t > $x[0];
    }

    public static function ID(string $from, $fail = -1) {
        return File::open(self::x($from))->import([$fail])[0];
    }

    private static function x(string $s) {
        $s = strtr($s, '/', DS);
        if (is_dir($s) || !file_exists($s)) {
            $f = CACHE . DS . dechex(crc32($s));
            if (!file_exists($f)) {
                File::put("")->saveTo($f, 0600);
            }
            $f .= '.php';
        } else {
            $f = str_replace(ROOT, CACHE, $s) . '.php';
        }
        self::$cache[$f] = file_exists($f) ? filemtime($f) : 0;
        return $f;
    }

}