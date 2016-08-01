<?php

abstract class __ {

    // Method(s) ...
    public static $_ = [];

    // Show the added method(s)
    public static function kin($kin = null, $fail = false, $origin = false) {
        $c = static::class;
        if ($kin !== null) {
            if (!isset(self::$_[0][$c][$kin])) {
                $output = self::$_[1][$c][$kin] ?? $fail;
                return $origin && method_exists($this, $kin) ? 1 : $output;
            }
            return $fail;
        }
        return !empty(self::$_[1][$c]) ? self::$_[1][$c] : $fail;
    }

    // Add new method with `__::plug('foo')`
    public static function plug($kin, $fn) {
        self::$_[1][static::class][$kin] = $fn;
    }

    // Remove the added method with `__::unplug('foo')`
    public static function unplug($kin) {
        if ($kin === true) {
            $this->_ = [];
        } else {
            $c = static::class;
            self::$_[0][$c][$kin] = 1;
            unset(self::$_[1][$c][$kin]);
        }
    }

    // Call the added method with `__::foo()`
    public static function __callStatic($kin, $lot = []) {
        $c = static::class;
        if (!isset(self::$_[1][$c][$kin])) {
            exit('Method <code>' . $c . '::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array(self::$_[1][$c][$kin], $lot);
    }

}