<?php

class Folder extends File {

    protected static function create_static($input, $consent = 0777) {
        foreach ((array) $input as $k => $v) {
            if (!file_exists($v)) {
                if (is_array($consent)) {
                    $c = $consent[$k] ?? end($consent);
                } else {
                    $c = $consent;
                }
                mkdir(To::path($v), $c, true);
            }
        }
    }

    protected static function exist_static($input, $fail = false) {
        $input = To::path($input);
        return is_dir($input) ? $input : $fail;
    }

}