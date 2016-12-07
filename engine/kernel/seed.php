<?php

class Seed extends Genome {

    public static $config = ['scope' => 'seed'];

    protected static function set_($k, $v = null) {
        if (__is_anemon__($k)) {
            foreach ($k as $kk => $vv) {
                $GLOBALS[self::$config['scope']][$kk] = $vv;
            }
        } else {
            $GLOBALS[self::$config['scope']][$k] = $v;
        }
        return new static;
    }

    protected static function get_($k = null, $fail = false) {
        $v = $GLOBALS[self::$config['scope']];
        if ($k === null) return $v ?? $fail;
        return array_key_exists($k, $v) ? $v[$k] : $fail;
    }

    protected static function reset_($k = null) {
        if ($k !== null) {
            unset($GLOBALS[self::$config['scope']][$k]);
        } else {
            $GLOBALS[self::$config['scope']] = [];
        }
        return new static;
    }

}