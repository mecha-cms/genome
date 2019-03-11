<?php

final class Lot extends Genome {

    public static function get($key = null) {
        $out = $GLOBALS['_' . md5(__FILE__)] ?? [];
        if (isset($key)) {
            return $out[$key] ?? null;
        }
        return $out;
    }

    public static function reset($key = null) {
        $scope = '_' . md5(__FILE__);
        if (is_array($key)) {
            foreach ($key as $v) {
                self::reset($v);
            }
        } else if (isset($key)) {
            unset($GLOBALS[$scope][$key]);
        } else {
            $GLOBALS[$scope] = [];
        }
    }

    public static function set($key, $value = null) {
        $scope = '_' . md5(__FILE__);
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                $GLOBALS[$scope][$k] = $v;
            }
        } else {
            $GLOBALS[$scope][$key] = $value;
        }
    }

}