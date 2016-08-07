<?php

class Is extends Socket {

    protected static $bucket = [];

    // Initialize ...
    public static function this($input) {
        self::$bucket = $input;
        return new static;
    }

    // @ditto
    public static function these(...$input) {
        self::$bucket = count((array) $input) === 1 ? (array) a($input) : $input;
        return new static;
    }

    // Check if `self::$bucket` contains `$s`
    public static function has($s, $all = false, $x = X) {
        $input = $x . implode($x . self::$bucket) . $x;
        if (is_array($s)) {
            if (!$all) {
                foreach ($s as $v) {
                    if (strpos($input, $x . $v . $x) !== false) {
                        return true;
                    }
                }
                return false;
            } else {
                $pass = 0;
                foreach ($s as $v) {
                    if (strpos($input, $x . $v . $x) !== false) {
                        $pass++;
                    }
                }
                return $pass === count($s);
            }
        }
        return strpos($input, $x . $s . $x) !== false;
    }

    // Check for empty string, array or object
    public static function void($x) {
        return (
            $x === "" ||
            is_string($x) && trim($x) === "" ||
            is_array($x) && empty($x) ||
            is_object($x) && empty((array) $x)
        );
    }

    // Check for IP address
    public static function ip($x) {
        return filter_var($x, FILTER_VALIDATE_IP);
    }

    // Check for URL address
    public static function url($x) {
        return filter_var($x, FILTER_VALIDATE_URL);
    }

    // Check for email address
    public static function email($x) {
        return filter_var($x, FILTER_VALIDATE_EMAIL);
    }

    // Check for valid boolean value
    public static function toggle($x) {
        return filter_var($x, FILTER_VALIDATE_BOOLEAN);
    }

    // Is equal to `$x`
    public static function eq($x) {
        return q(self::$bucket) === $x;
    }

    // Is less than `$x`
    public static function lt($x) {
        return q(self::$bucket) < $x;
    }

    // Is greater than `$x`
    public static function gt($x) {
        return q(self::$bucket) > $x;
    }

    // Is less than or equal to `$x`
    public static function lte($x) {
        return q(self::$bucket) <= $x;
    }

    // Is greater than or equal to `$x`
    public static function gte($x) {
        return q(self::$bucket) >= $x;
    }

}