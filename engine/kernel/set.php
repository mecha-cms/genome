<?php

class Set extends Genome {

    public static function __callStatic($kin, $lot = []) {
        if (!self::_($kin)) {
            $id = '_' . strtoupper($kin);
            $key = array_shift($lot);
            $value = array_shift($lot);
            if (is_array($key)) {
                Anemon::extend($GLOBALS[$id], $key);
            } else {
                Anemon::set($GLOBALS[$id], $key, $value);
            }
            return new static;
        }
        return parent::__callStatic($kin, $lot);
    }

}