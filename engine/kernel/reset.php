<?php

final class Reset extends Genome {

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $id = '_' . strtoupper($kin);
        $key = array_shift($lot);
        if (!isset($key)) {
            $GLOBALS[$id] = [];
        } else if (is_array($key)) {
            foreach ($key as $v) {
                unset($GLOBALS[$id][$v]);
            }
        } else {
            Anemon::reset($GLOBALS[$id], $key);
        }
    }

}