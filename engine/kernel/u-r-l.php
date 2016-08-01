<?php

class URL extends __ {

    // `http://user:pass@host:9090/path?key=value#hash`
    public static function extract($key = null, $input = null, $fail = false) {
        if (!$input) {
            $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443 ? 'https' : 'http';
            $protocol = $scheme . '://';
            $host = $_SERVER['HTTP_HOST'];
            $sub = trim(To::url(Path::D($_SERVER['SCRIPT_NAME'])), '/');
            $url = rtrim($protocol . $host  . '/' . $sub, '/');
            $s = preg_replace('#[<>"]|[?&].*$#', "", trim($_SERVER['QUERY_STRING'], '/')); // Remove HTML tag(s) and query string(s) from URL
            $path = trim(str_replace('/?', '?', $_SERVER['REQUEST_URI']), '/') === $sub . '?' . trim($_SERVER['QUERY_STRING'], '/') ? "" : $s;
            $current = rtrim($url . '/' . $path, '/');
            $output = [
                'scheme' => $scheme,
                'protocol' => $protocol,
                'host' => $host,
                'port' => (int) $_SERVER['SERVER_PORT'],
                'user' => null,
                'pass' => null,
                'sub' => $sub,
                'url' => $url,
                'path' => $path,
                'query' => null,
                'current' => $current,
                'origin' => null,
                'hash' => null
            ];
            return $key ? $output[$key] ?? $fail : $output;
        }
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
            'origin' => null,
            'hash' => $h ? '#' . $h : ""
        ];
        return $key ? $output[$key] ?? $fail : $output;
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
            ], trim(self::url() . '/' . $url, '/'));
        }
        return $url;
    }

    public static function short($url, $root = true) {
        $url = str_replace([X . self::protocol() . self::host(), X], "", X . $url);
        return $root ? $url : ltrim($url, '/');
    }

    public static function __callStatic($kin, $lot = []) {
        if (!self::kin($kin)) {
            return self::extract($kin, null, array_shift($lot));
        }
        return parent::__callStatic($kin, $lot);
    }

}