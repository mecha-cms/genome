<?php

class HTML extends Genome {

    protected static $lot;

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$lot)) {
            self::$lot = new Genome\Union;
        }
        if (method_exists(self::$lot, $kin)) {
            return call_user_func_array([self::$lot, $kin], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}