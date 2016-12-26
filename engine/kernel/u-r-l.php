<?php

class URL extends Genome {

    public static function long($url, $root = true) {
        if (!is_string($url)) return $url;
        $a = __url__();
        // Relative to the root domain
        if ($root && strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return trim($a['protocol'] . $a['host'] . '/' . ltrim($url, '/'), '/');
        }
        if (
            strpos($url, '://') === false &&
            strpos($url, '//') !== 0 &&
            strpos($url, '?') !== 0 &&
            strpos($url, '&') !== 0 &&
            strpos($url, '#') !== 0 &&
            strpos($url, 'javascript:') !== 0
        ) {
            return str_replace([
                '\\',
                '/?',
                '/&',
                '/#'
            ], [
                '/',
                '?',
                '&',
                '#'
            ], trim($a['url'] . '/' . $url, '/'));
        }
        return $url;
    }

    public static function short($url, $root = true) {
        $a = __url__();
        $url = str_replace([X . $a['protocol'] . $a['host'], X], "", X . $url);
        return $root ? $url : ltrim($url, '/');
    }

    public static function __callStatic($kin, $lot) {
        $a = __url__();
        if (!self::kin($kin)) {
            $fail = array_shift($lot);
            return array_key_exists($kin, $a) ? $a[$kin] : ($fail ? $fail : false);
        }
        return parent::__callStatic($kin, $lot);
    }

    public function __construct() {
        foreach (__url__() as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function __get($key) {
        return isset($this->{$key}) ? $this->{$key} : false;
    }

    public function __set($key, $value = null) {
        $this->{$key} = $value;
    }

    public function __toString() {
        return $this->url;
    }

}