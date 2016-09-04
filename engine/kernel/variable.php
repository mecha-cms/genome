<?php

class Variable extends Genome {

    protected static $lot;

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$lot)) {
            $unit = ['{{', '}}', '/', '[\w:.-]+'];
            $data = ['=', '"', '"', ' ', '[\w:.-]+'];
            self::$lot = new Seed\Union($unit, $data);
        }
        if (method_exists(self::$lot, $kin)) {
            return call_user_func_array([self::$lot, $kin], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}