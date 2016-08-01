<?php

class To extends __ {

    protected static $fn = [];

    public static function safe(...$lot) {
        $c = static::class;
        if (count($lot) === 2 && is_callable($lot[1])) {
            self::$fn[$c][$lot[0]] = $lot[1];
            return true;
        }
        $id = array_shift($lot);
        $input = array_shift($lot);
        if (isset(self::$fn[$c][$id])) {
            return call_user_func_array(self::$fn[$c][$id], $lot);
        }
        return $input;
    }

}