<?php

class Request extends Genome {

    public static function get($key = null) {
        $a = $GLOBALS['_' . strtoupper(static::class)];
        return e(isset($key) ? get($a, $key) : ($a ?? []));
    }

    public static function is(string $name = null, string $key = null) {
        $r = strtoupper($_SERVER['REQUEST_METHOD']);
        if (isset($name)) {
            $name = strtoupper($name);
            if (isset($key)) {
                return null !== get($GLOBALS['_' . $name], $key);
            }
            return $name === $r;
        }
        return ucfirst(strtolower($r));
    }

    public static function let($key = null) {
        $k = strtoupper(static::class);
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key)) {
            let($GLOBALS['_' . $k], $key);
        } else {
            $GLOBALS['_' . $k] = [];
        }
    }

    public static function set(string $key, $value) {
        set($GLOBALS['_' . strtoupper(static::class)], $key, $value);
    }

}