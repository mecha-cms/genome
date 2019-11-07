<?php

class Server extends Genome {

    public static function __callStatic(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        return $GLOBALS['_' . strtoupper(static::class)][strtr(strtoupper($kin), '-', '_')] ?? null;
    }

    public static function get($key = null) {
        $a = $GLOBALS['_' . strtoupper(static::class)] ?? [];
        return e(isset($key) ? get($a, strtr(strtoupper($key), '-', '_')) : ($a ?? []));
    }

    public static function let($key = null) {
        $k = strtoupper(static::class);
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key)) {
            let($GLOBALS['_' . $k], strtr(strtoupper($key), '-', '_'));
        } else {
            $GLOBALS['_' . $k] = [];
        }
    }

    public static function set(string $key, $value) {
        set($GLOBALS['_' . strtoupper(static::class)], strtr(strtoupper($key), '-', '_'), $value);
    }

}