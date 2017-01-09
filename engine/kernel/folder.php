<?php

class Folder extends File {

    public static function set($input, $consent = 0777) {
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
    }

    public static function exist($input, $fail = false) {
        $file = parent::exist(rtrim($input, DS . '/'));
        return $file && is_dir($file) ? $file : $fail;
    }

}