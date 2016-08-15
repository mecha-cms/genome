<?php

class HTML extends Genome {

    protected static $slot;

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$slot)) {
            self::$slot = Genome\Union::_();
        }
        if (method_exists(self::$slot, $kin)) {
            return call_user_func_array([self::$slot, $kin], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}