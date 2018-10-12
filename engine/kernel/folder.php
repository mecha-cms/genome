<?php

class Folder extends File {

    public static function set($input, $consent = 0775) {
        foreach ((array) $input as $k => $v) {
            if (!file_exists($v)) {
                if (is_array($consent)) {
                    $c = array_key_exists($k, $consent) ? $consent[$k] : end($consent);
                } else {
                    $c = $consent;
                }
                mkdir(To::path($v), $c, true);
            }
        }
        return $input;
    }

    public static function exist($input, $fail = false) {
        $file = parent::exist(rtrim($input, DS . '/'));
        return $file && is_dir($file) ? $file : $fail;
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