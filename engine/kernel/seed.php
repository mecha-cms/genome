<?php

class Seed extends Genome {

    public static $scope = 'var';

    public static function set($k, $v = null) {
        if (__is_anemon__($k)) {
            foreach ($k as $kk => $vv) {
                $GLOBALS[self::$scope][$kk] = $vv;
            }
        } else {
            $GLOBALS[self::$scope][$k] = $v;
        }
        return new static;
    }

    public static function get($k = null, $fail = false) {
        if ($k === null) return $GLOBALS[self::$scope] ?? $fail;
        return array_key_exists($k, $GLOBALS[self::$scope]) ? $GLOBALS[self::$scope][$k] : $fail;
    }

    public static function reset($k = null) {
        if ($k !== null) {
            unset($GLOBALS[self::$scope][$k]);
        } else {
            $GLOBALS[self::$scope] = [];
        }
        return new static;
    }

}