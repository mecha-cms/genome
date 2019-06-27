<?php

final class Set extends Genome {

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
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
                // Extent
                $GLOBALS[$id] = array_replace_recursive($GLOBALS[$id], $key);
            }
        // `Set::post('foo', 'bar')`
        } else {
            set($GLOBALS[$id], $key, $value);
        }
    }

}