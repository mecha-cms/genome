<?php

class HTML extends Genome {

    protected static $lot;

    public static $begin = "";
    public static $end = N; 

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$lot)) {
            self::$lot = new Seed\Union;
        }
        if (!self::$lot->kin($kin)) {
            return self::$lot->__call($kin, $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}