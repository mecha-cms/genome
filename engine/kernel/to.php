<?php

class To extends Genome {

    public static function __callStatic($kin, $lot = []) {
        if (!self::_($kin)) return $lot[0]; // Disable error message!
        return parent::__callStatic($kin, $lot);
    }

}