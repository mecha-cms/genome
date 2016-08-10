<?php

class URL extends Socket {

    // `http://user:pass@host:9090/path?key=value#hash`
    public static function extract($key = null, $input = null, $fail = false) {
        return Config::url($key, $fail);
        $s = parse_url($input);
        $q = trim(str_replace('&amp;', '&', $s['query']));
        $h = trim($s['fragment']);
        $output = [
            'scheme' => $s['scheme'],
            'protocol' => $s['scheme'] . '://',
            'host' => $s['host'],
            'port' => $s['port'],
            'user' => $s['user'],
            'pass' => $s['pass'],
            'sub' => null,
            'url' => $s['scheme'] . '://' . $s['host'],
            'path' => trim($s['path'], '/'),
            'query' => $q ? '?' . $q : "",
            'current' => trim(preg_replace('#[?&\#].*$#', "", $input), '/'),
            'origin' => Session::get('url.origin', null),
            'hash' => $h ? '#' . $h : ""
        ];
        return $key ? ($output[$key] ?? $fail) : o($output);
    }

    public static function long($url, $root = true) {
        if(!is_string($url)) return $url;
        // Relative to the root domain
        if($root && strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return trim(self::protocol() . self::host() . '/' . ltrim($url, '/'), '/');
        }
        if(
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
            ], trim(self::get() . '/' . $url, '/'));
        }
        return $url;
    }

    public static function short($url, $root = true) {
        $url = str_replace([X . self::protocol() . self::host(), X], "", X . $url);
        return $root ? $url : ltrim($url, '/');
    }

    public static function scheme() {
        return self::extract('scheme');
    }

    public static function protocol() {
        return self::extract('protocol');
    }
    
    public static function host() {
        return self::extract('host');
    }

    public static function port() {
        return self::extract('port');
    }

    public static function user() {
        return self::extract('user');
    }

    public static function pass() {
        return self::extract('pass');
    }

    public static function sub() {
        return self::extract('sub');
    }

    public static function get() {
        return self::extract('url');
    }

    public static function path() {
        return self::extract('path');
    }

    public static function query() {
        return self::extract('query');
    }

    public static function current() {
        return self::extract('current');
    }

    public static function origin() {
        return self::extract('origin');
    }

    public static function hash() {
        return self::extract('hash');
    }

}