<?php

class Mecha extends Genome {

    // Current version
    protected static $version_ = '2.0.0';

    // Compare with current version
    protected static function version_($v = null, $c = null) {
        if ($c === null) {
            $c = self::$version_;
        }
        if ($v === null) {
            return $c;
        }
        $v = explode(' ', $v);
        if (count($v) === 1) {
            array_unshift($v, '=');
        }
        return version_compare($c, $v[1], $v[0]);
    }

}