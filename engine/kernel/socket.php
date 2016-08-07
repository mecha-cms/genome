<?php

abstract class Socket {

    // Method(s) ...
    public static $_ = [];

    // Get class instance ...
    public static function _(...$lot) {
        $c = static::class;
        return new $c($lot);
    }

    // Show the added method(s)
    public static function kin($kin = null, $fail = false, $origin = false) {
        $c = static::class;
        if ($kin !== null) {
            if (!isset(self::$_[0][$c][$kin])) {
                $output = self::$_[1][$c][$kin] ?? $fail;
                return $origin && method_exists($c, $kin) ? 1 : $output;
            }
            return $fail;
        }
        return !empty(self::$_[1][$c]) ? self::$_[1][$c] : $fail;
    }

    // Add new method with `Socket::plug('foo')`
    public static function plug($kin, $fn) {
        self::$_[1][static::class][$kin] = $fn;
    }

    // Remove the added method with `Socket::unplug('foo')`
    public static function unplug($kin) {
        if ($kin === true) {
            self::$_ = [];
        } else {
            $c = static::class;
            self::$_[0][$c][$kin] = 1;
            unset(self::$_[1][$c][$kin]);
        }
    }

    // Call the added method with `Socket::foo()`
    public static function __callStatic($kin, $lot = []) {
        $c = static::class;
        if (!isset(self::$_[1][$c][$kin])) {
            echo('Method <code>' . $c . '::' . $kin . '()</code> does not exist.');
			return false;
        }
        return call_user_func_array(self::$_[1][$c][$kin], $lot);
    }

}