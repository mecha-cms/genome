<?php

final class To extends Genome {

    public static function __callStatic(string $kin, array $lot = []) {
        return self::_($kin) ? parent::__callStatic($kin, $lot) : $lot[0];
    }

}