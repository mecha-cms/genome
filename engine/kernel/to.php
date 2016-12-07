<?php

class To extends Genome {

    protected static $fn_ = [];

    protected static function safe_(...$lot) {
        $c = static::class;
        if (count($lot) === 2 && is_callable($lot[1])) {
            self::$fn_[$c][$lot[0]] = $lot[1];
            return true;
        }
        $id = array_shift($lot);
        $input = array_shift($lot);
        if (isset(self::$fn_[$c][$id])) {
            return call_user_func_array(self::$fn_[$c][$id], $lot);
        }
        return $input;
    }

}