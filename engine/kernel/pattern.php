<?php

class Pattern extends Union {

    protected static $lot;

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$lot)) {
            self::$lot = new static([
                // 0 => [
                //     0 => ['\{\{', '\}\}', '\/'],
                //     1 => ['\=', '\"', '\"', '\s+']
                // ],
                1 => [
                    0 => ['{{', '}}', '/'],
                    1 => ['=', '"', '"', ' ']
                ]
            ]);
        }
        if (method_exists(self::$lot, $kin)) {
            return call_user_func_array([self::$lot, $kin], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}