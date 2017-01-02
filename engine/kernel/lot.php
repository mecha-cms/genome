<?php

class Lot extends Genome {

    public static function set($k, $v = null, $scope = null) {
        if (__is_anemon__($k)) {
            $scope = $v ?: static::class;
            foreach ($k as $kk => $vv) {
                $GLOBALS[$scope][$kk] = $vv;
            }
        } else {
            $GLOBALS[$scope ?: static::class][$k] = $v;
        }
        return new static;
    }

    public static function get($k = null, $fail = false, $scope = null) {
        $scope = $scope ?: static::class;
        $v = isset($GLOBALS[$scope]) ? $GLOBALS[$scope] : [];
        if (isset($k)) {
            return array_key_exists($k, $v) ? $v[$k] : $fail;
        }
        return isset($v) ? $v : $fail;
    }

    public static function reset($k = null, $scope = null) {
        $scope = $scope ?: static::class;
        if (isset($k)) {
            unset($GLOBALS[$scope][$k]);
        } else {
            $GLOBALS[$scope] = [];
        }
        return new static;
    }

}