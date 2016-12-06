<?php

class Pattern extends Union {

    protected static $lot;

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$lot)) {
            $unit = ['{{', '}}', '/', '[\w:.-]+'];
            $data = ['=', '"', '"', ' ', '[\w:.-]+'];
            self::$lot = new Union([
                // 0 => [
                //     0 => ['\{\{', '\}\}', '\/'],
                //     1 => ['\=', '\"', '\"', '\s+']
                // ],
                1 => [
                    0 => ['{{', '}}', '/', '[\w:.-]+'],
                    1 => ['=', '"', '"', ' ', '[\w:.-]+']
                ]
            ]);
        }
        if (method_exists(self::$lot, $kin)) {
            return call_user_func_array([self::$lot, $kin], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}