<?php

class URL extends Genome {

    public static function long($url, $root = true) {
        if (!is_string($url)) return $url;
        $a = __url__();
        $b = false;
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $url = ltrim($url, '/');
            $b = true; // relative to the root domain
        }
        if (
            strpos($url, '://') === false &&
            strpos($url, '//') !== 0 &&
            strpos($url, '?') !== 0 &&
            strpos($url, '&') !== 0 &&
            strpos($url, '#') !== 0 &&
            strpos($url, 'javascript:') !== 0
        ) {
            return trim(($root && $b ? $a['protocol'] . $a['host'] : $a['url']) . '/' . self::_fix($url), '/');
        }
        return self::_fix($url);
    }

    public static function short($url, $root = true) {
        $a = __url__();
        if (strpos($url, '//') === 0 && strpos($url, '//' . $a['host']) !== 0) {
            return $url; // ignore external URL
        }
        $url = X . $url;
        if ($root) {
            return str_replace([X . $a['protocol'] . $a['host'], X . '//' . $a['host'], X], "", $url);
        }
        return ltrim(str_replace([X . $a['url'], X . '//' . rtrim($a['host'] . '/' . $a['directory'], '/'), X], "", $url), '/');
    }

    protected static function _fix($s) {
        return str_replace(['\\', '/?', '/&', '/#'], ['/', '?', '&', '#'], $s);
    }

    public static function __callStatic($kin, $lot) {
        $a = __url__();
        if (!self::kin($kin)) {
            $fail = array_shift($lot) ?: false;
            return array_key_exists($kin, $a) ? $a[$kin] : $fail;
        }
        return parent::__callStatic($kin, $lot);
    }

    public function __construct() {
        foreach (__url__() as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function __set($key, $value = null) {
        $this->{$key} = $value;
    }

    public function __get($key) {
        return isset($this->{$key}) ? $this->{$key} : "";
    }

    public function __unset($key) {
        unset($this->{$key});
    }

    public function __toString() {
        return $this->url;
    }

}