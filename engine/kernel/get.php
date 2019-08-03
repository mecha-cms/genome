<?php

final class Get extends Genome {

    public static function files(string $folder = PAGE, string $x = null) {
        $files = [];
        foreach (g($folder, $x) as $v) {
            if (pathinfo($v, PATHINFO_FILENAME) === "") {
                continue;
            }
            $files[] = $v;
        }
        return new Files($files);
    }

    public static function IP() {
        $for = 'HTTP_X_FORWARDED_FOR';
        if (array_key_exists($for, $_SERVER) && !empty($_SERVER[$for])) {
            if (strpos($_SERVER[$for], ',') > 0) {
                $ip = trim(strstr($ip[0], ',', true));
            } else {
                $ip = $_SERVER[$for];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return Is::IP($ip) ? $ip : null;
    }

    public static function UA() {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $id = '_' . strtoupper($kin);
        $key = array_shift($lot);
        if (isset($key)) {
            return get($GLOBALS[$id], $key);
        } else if (is_array($key)) {
            $out = [];
            foreach ($key as $k => $v) {
                $out[$k] = get($GLOBALS[$id], $k) ?? $v;
            }
            return $out;
        }
        return $GLOBALS[$id] ?? [];
    }

}