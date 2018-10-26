<?php

class Is extends Genome {

    protected $lot = [];

    // Initialize…
    public static function this($in) {
        return new static($in);
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
    public static function eMail($x) {
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

    // Is equal to `$x`
    public function EQ($x) {
        return q($this->lot) === $x;
    }

    // Is less than `$x`
    public function LT($x) {
        return q($this->lot) < $x;
    }

    // Is greater than `$x`
    public function GT($x) {
        return q($this->lot) > $x;
    }

    // Is less than or equal to `$x`
    public function LE($x) {
        return q($this->lot) <= $x;
    }

    // Is greater than or equal to `$x`
    public function GE($x) {
        return q($this->lot) >= $x;
    }

    public function __construct($in = []) {
        $this->lot = $in;
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