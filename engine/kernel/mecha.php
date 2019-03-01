<?php

final class Mecha extends Genome {

    // Current version
    const version = '2.2.0';

    // Compare with current version
    public static function version(string $version = null, string $compare = null) {
        if (!isset($compare)) {
            $compare = self::version;
        }
        if (!isset($version)) {
            return $compare;
        }
        $version = explode(' ', $version);
        if (count($version) === 1) {
            array_unshift($version, '=');
        }
        return version_compare($compare, $version[1], $version[0]);
    }

}