<?php

class Is extends Genome {

    protected $bucket = [];

    // Initialize…
    public static function this($input) {
        return new static($input);
    }

    // Any of these…
    public static function any(array $input, ...$lot) {
        foreach ($input as $v) {
            if ($output = call_user_func('self::' . $v, ...$lot)) {
                return $output;
            }
        }
        return false;
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
    public static function IP($x) {
        return filter_var($x, FILTER_VALIDATE_IP);
    }

    // Check for URL address
    public static function URL($x) {
        return filter_var($x, FILTER_VALIDATE_URL);
    }

    // Check for valid local path address (whether it is exists or not)
    public static function path($x, $e = false) {
        if (!is_string($x)) return false;
        return strpos($x, ROOT) === 0 && strpos($x, "\n") === false && (!$e || file_exists($x));
    }

    // Check for email address
    public static function EMail($x) {
        return filter_var($x, FILTER_VALIDATE_EMAIL);
    }

    // Check for valid boolean value
    public static function toggle($x) {
        return filter_var($x, FILTER_VALIDATE_BOOLEAN);
    }

    // Check for valid file name
    public static function file($x) {
        return is_string($x) && strlen($x) <= 260 && realpath($x) && is_file($x);
    }

    // Check for valid folder name
    public static function folder($x) {
        return is_string($x) && strlen($x) <= 260 && realpath($x) && is_dir($x);
    }

    // Check if `$this->bucket` contains `$s`
    public function has($s, $all = false, $x = X) {
        $input = $x . implode($x, $this->bucket) . $x;
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
                        ++$pass;
                    }
                }
                return $pass === count($s);
            }
        }
        return strpos($input, $x . $s . $x) !== false;
    }

    // Is equal to `$x`
    public function EQ($x) {
        return q($this->bucket) === $x;
    }

    // Is less than `$x`
    public function LT($x) {
        return q($this->bucket) < $x;
    }

    // Is greater than `$x`
    public function GT($x) {
        return q($this->bucket) > $x;
    }

    // Is less than or equal to `$x`
    public function LE($x) {
        return q($this->bucket) <= $x;
    }

    // Is greater than or equal to `$x`
    public function GE($x) {
        return q($this->bucket) >= $x;
    }

    public function __construct($input) {
        $this->bucket = $input;
    }

    public function __call($kin, $lot = []) {
        if (!self::_($kin)) return null; // Disable error message!
        return parent::__call($kin, $lot);
    }

    public static function __callStatic($kin, $lot = []) {
        if (!self::_($kin)) return null; // Disable error message!
        return parent::__callStatic($kin, $lot);
    }

}