<?php

class Get extends Genome {

    public static function IP($fail = false) {
        $for = 'HTTP_X_FORWARDED_FOR';
        if (array_key_exists($for, $_SERVER) && !empty($_SERVER[$for])) {
            if (strpos($_SERVER[$for], ',') > 0) {
                $ip = explode(',', $_SERVER[$for]);
                $ip = trim($ip[0]);
            } else {
                $ip = $_SERVER[$for];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return Is::IP($ip) ? $ip : $fail;
    }

    public static function UA($fail = false) {
        return !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : $fail;
    }

}