<?php

class To extends Genome {

    protected static $fn_static = [];

    protected static function safe_static(...$lot) {
        $c = static::class;
        if (count($lot) === 2 && is_callable($lot[1])) {
            self::$fn_static[$c][$lot[0]] = $lot[1];
            return true;
        }
        $id = array_shift($lot);
        $input = array_shift($lot);
        if (isset(self::$fn_static[$c][$id])) {
            return call_user_func_array(self::$fn_static[$c][$id], $lot);
        }
        return $input;
    }

}