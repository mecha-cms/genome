<?php

class HTML extends Union {

    protected static $lot;

    public static $begin = "";
    public static $end = N; 

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$lot)) {
            self::$lot = new HTML;
        }
        return call_user_func_array([self::$lot, $kin], $lot);
    }

}