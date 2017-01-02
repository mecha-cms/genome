<?php

class Lot extends Genome {

    public static $config = ['scope' => 'seed'];

    public static function set($k, $v = null) {
        if (__is_anemon__($k)) {
            foreach ($k as $kk => $vv) {
                $GLOBALS[self::$config['scope']][$kk] = $vv;
            }
        } else {
            $GLOBALS[self::$config['scope']][$k] = $v;
        }
        return new static;
    }

    public static function get($k = null, $fail = false) {
        $v = $GLOBALS[self::$config['scope']];
        if (!isset($k)) return isset($v) ? $v : $fail;
        return array_key_exists($k, $v) ? $v[$k] : $fail;
    }

    public static function reset($k = null) {
        if (isset($k)) {
            unset($GLOBALS[self::$config['scope']][$k]);
        } else {
            $GLOBALS[self::$config['scope']] = [];
        }
        return new static;
    }

}