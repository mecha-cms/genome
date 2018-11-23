<?php

class Folder extends File {

    public function __toString() {
        return "";
    }

    public static function create($path, $consent = 0775) {
        foreach ((array) $path as $k => &$v) {
            if (!file_exists($v) || is_file($v)) {
                if (is_array($consent)) {
                    $c = array_key_exists($k, $consent) ? $consent[$k] : end($consent);
                } else {
                    $c = $consent;
                }
                mkdir($v = To::path($v), $c, true);
            }
        }
        return $path;
    }

    // Alias for `create`
    public static function set($path, $consent = 0755) {
        return self::create($path, $consent);
    }

    public static function exist($path, $fail = false) {
        $path = parent::exist(rtrim($path, DS . '/'));
        return $path && is_dir($path) ? $path : $fail;
    }

    public static function size($folder, $unit = null, $prec = 2) {
        if (!is_dir($folder)) return false;
        if (!glob($folder . DS . '*', GLOB_NOSORT)) {
            return parent::size(0, $unit, $prec);
        }
        $sizes = 0;
        foreach (parent::explore([$folder, 1], true, []) as $k => $v) {
            $sizes += filesize($k);
        }
        return parent::size($sizes, $unit, $prec);
    }

}