<?php

class URL extends Genome {

    protected static function long_static($url, $root = true) {
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

    protected static function short_static($url, $root = true) {
        $a = __url__();
        $url = str_replace([X . $a['protocol'] . $a['host'], X], "", X . $url);
        return $root ? $url : ltrim($url, '/');
    }

    public static function __callStatic($kin, $lot) {
        $a = __url__();
        if (!self::kin($kin)) {
            return $a[$kin] ?? array_shift($lot) ?? false;
        }
        return parent::__callStatic($kin, $lot);
    }

    public function __construct() {
        foreach (__url__() as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function __get($key) {
        return $this->{$key} ?? false;
    }

    public function __set($key, $value = null) {
        $this->{$key} = $value;
    }

    public function __toString() {
        return $this->url;
    }

}