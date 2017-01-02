<?php

class Block extends Union {

    protected static $lot;

    public static function __callStatic($kin, $lot) {
        if (!isset(self::$lot)) {
            self::$lot = new static(Extend::state(Path::D(__DIR__, 2), null, []));
        }
        if (method_exists(self::$lot, $kin)) {
            return call_user_func_array([self::$lot, $kin], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}