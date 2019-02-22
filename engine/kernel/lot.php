<?php

class Lot extends Genome {

    public static function get($key = null) {
        $data = $GLOBALS['.' . md5(static::class)] ?? [];
        if (isset($key)) {
            return $data[$key] ?? null;
        }
        return $data;
    }

    public static function reset($key = null) {
        $scope = '.' . md5(static::class);
        if (isset($key)) {
            if (is_array($key)) {
                foreach ($key as $v) {
                    self::reset($v);
                }
            } else {
                unset($GLOBALS[$scope][$key]);
            }
        } else {
            $GLOBALS[$scope] = [];
        }
    }

    public static function set($key, $value = null) {
        $scope = '.' . md5(static::class);
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                $GLOBALS[$scope][$k] = $v;
            }
        } else {
            $GLOBALS[$scope][$key] = $value;
        }
    }

}