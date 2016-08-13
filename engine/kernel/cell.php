<?php

class Cell extends Socket {

    protected static $union;

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$union)) {
            self::$union = Genome\Union::start();
        }
        if (method_exists(self::$union, $kin)) {
            return call_user_func_array([self::$union, $kin], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}