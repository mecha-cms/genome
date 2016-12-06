<?php

class Mecha extends Genome {

    // Current version
    protected static $version_static = '2.0.0';

    // Compare with current version
    protected static function version_static($v = null, $c = null) {
        if ($c === null) {
            $c = self::$version_static;
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