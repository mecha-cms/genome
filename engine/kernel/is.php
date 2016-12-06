<?php

class Is extends Genome {

    protected static $bucket_static = [];

    // Initialize ...
    protected static function this_static($input) {
        self::$bucket_static = $input;
        return new static;
    }

    // @ditto
    protected static function these_static(...$input) {
        self::$bucket_static = count((array) $input) === 1 ? (array) a($input) : $input;
        return new static;
    }

    // Check if `self::$bucket_static` contains `$s`
    protected static function has_static($s, $all = false, $x = X) {
        $input = $x . implode($x . self::$bucket_static) . $x;
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
    protected static function void_static($x) {
        return (
            $x === "" ||
            is_string($x) && trim($x) === "" ||
            is_array($x) && empty($x) ||
            is_object($x) && empty((array) $x)
        );
    }

    // Check for IP address
    protected static function ip_static($x) {
        return filter_var($x, FILTER_VALIDATE_IP);
    }

    // Check for URL address
    protected static function url_static($x) {
        return filter_var($x, FILTER_VALIDATE_URL);
    }

    // Check for valid local path address (whether it is exists or not)
    protected static function path_static($x, $e = false) {
        if (!is_string($x)) return false;
        return strpos($x, ROOT) === 0 && strpos($x, "\n") === false && (!$e || file_exists($x));
    }

    // Check for email address
    protected static function email_static($x) {
        return filter_var($x, FILTER_VALIDATE_EMAIL);
    }

    // Check for valid boolean value
    protected static function toggle_static($x) {
        return filter_var($x, FILTER_VALIDATE_BOOLEAN);
    }

    // Is equal to `$x`
    protected static function eq_static($x) {
        return q(self::$bucket_static) === $x;
    }

    // Is less than `$x`
    protected static function lt_static($x) {
        return q(self::$bucket_static) < $x;
    }

    // Is greater than `$x`
    protected static function gt_static($x) {
        return q(self::$bucket_static) > $x;
    }

    // Is less than or equal to `$x`
    protected static function lte_static($x) {
        return q(self::$bucket_static) <= $x;
    }

    // Is greater than or equal to `$x`
    protected static function gte_static($x) {
        return q(self::$bucket_static) >= $x;
    }

}