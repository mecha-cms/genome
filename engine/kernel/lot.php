<?php

class Lot extends Genome {

    public static function set($key, $value = null) {
        $scope = '.' . md5(static::class);
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                $GLOBALS[$scope][$k] = $v;
            }
        } else {
            $GLOBALS[$scope][$key] = $value;
        }
        return new static;
    }

    public static function get($key = null, $fail = false) {
        $data = $GLOBALS['.' . md5(static::class)] ?? [];
        if (isset($key)) {
            return array_key_exists($key, $data) ? $data[$key] : $fail;
        }
        return $data ?: $fail ?: [];
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
        return new static;
    }

}