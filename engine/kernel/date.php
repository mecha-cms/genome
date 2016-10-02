<?php

class Date extends Genome {

    public static $TZ = 'Asia/Jakarta';
    public static $formats = [];

    public static function format($input, $format = 'Y-m-d H:i:s') {
        if (is_callable($format)) {
            self::$formats[$input] = $format;
            return true;
        }
        $date = new Genome\Date($input);
        return $date->format($format);
    }

    public static function slug($input) {
        return self::format($input, 'Y-m-d-H-i-s');
    }

    public static function ago($input, $key = null, $fail = false, $compact = true) {
        $date = new Genome\Date($input);
        return $date->ago($key, $fail, $compact);
    }

    public static function GMT($input, $format = 'Y-m-d H:i:s') {
        $date = new Genome\Date($input);
        return $date->GMT($format);
    }

    public static function TZ($zone = 'Asia/Jakarta') {
        self::$TZ = $zone;
        return date_default_timezone_set($zone);
    }

}