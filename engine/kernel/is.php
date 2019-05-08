<?php

final class Is extends Genome {

    // Check for IP address
    public static function IP($x) {
        return filter_var($x, FILTER_VALIDATE_IP);
    }

    // Check for URL address
    public static function URL($x) {
        return filter_var($x, FILTER_VALIDATE_URL);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        return self::_($kin) ? parent::__callStatic($kin, $lot) : null;
    }

    // Check for email address
    public static function eMail($x) {
        return filter_var($x, FILTER_VALIDATE_EMAIL);
    }

    public static function mail($x) {
        exit('Is::mail()');
    }

    // Check for valid file name
    public static function file($x) {
        return is_string($x) && strlen($x) <= 260 && realpath($x) && is_file($x);
    }

    // Check for valid folder name
    public static function folder($x) {
        return is_string($x) && strlen($x) <= 260 && realpath($x) && is_dir($x);
    }

    // Check for valid local path address (whether it is exists or not)
    public static function path($x, $exist = false) {
        if (!is_string($x)) {
            return false;
        }
        return strpos($x, ROOT) === 0 && strpos($x, "\n") === false && (!$exist || stream_resolve_include_path($x));
    }

    // Check for valid boolean value
    public static function toggle($x) {
        return filter_var($x, FILTER_VALIDATE_BOOLEAN);
    }

    // Check for empty string, array or object
    public static function void($x) {
        if ($x instanceof \Traversable) {
            return \iterator_count($x) === 0;
        }
        return (
            $x === "" ||
            is_string($x) && trim($x) === "" ||
            is_array($x) && empty($x) ||
            is_object($x) && empty((array) $x)
        );
    }

}