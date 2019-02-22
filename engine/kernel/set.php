<?php

class Set extends Genome {

    public static function __callStatic(string $kin, array $lot = []) {
        if (!self::_($kin)) {
            $id = '_' . strtoupper($kin);
            $key = array_shift($lot);
            $value = array_shift($lot);
            if (is_array($key)) {
                // `Set::post(['foo' => 'bar'], false)`
                if ($value === false) {
                    // Replace
                    $GLOBALS[$id] = $key;
                // `Set::post(['foo' => 'bar'])`
                } else {
                    // Extend
                    $GLOBALS[$id] = extend($GLOBALS[$id], $key);
                }
            // `Set::post('foo', 'bar')`
            } else {
                Anemon::set($GLOBALS[$id], $key, $value);
            }
        }
        return parent::__callStatic($kin, $lot);
    }

}