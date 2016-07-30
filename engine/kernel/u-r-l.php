<?php

class URL extends DNA {

    // `http://user:pass@host:9090/path?key=value#hash`
    public function extract($key = null, $input = null) {
        if (!$input) {
            $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443 ? 'https' : 'http';
            $protocol = $scheme . '://';
            $host = $_SERVER['HTTP_HOST'];
            $sub = trim(Path::url(Path::D($_SERVER['SCRIPT_NAME'])), '/');
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
            return $key ? $output[$key] ?? null : $output;
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
            'path' => ltrim($s['path'], '/'),
            'query' => $q ? '?' . $q : "",
            'current' => trim(preg_replace('#[?&\#].*$#', "", $input), '/'),
            'origin' => null,
            'hash' => $h ? '#' . $h : ""
        ];
        return $key ? $output[$key] ?? null : $output;
    }

    public function path($url = X) {
        if ($url === X) {
            return $this->extract('path');
        }
        return str_replace([X . self::url(), '\\', '/', X], [ROOT, DS, DS, ""], X . $url);
    }

    public function get($key = null, $fail = false) {
        return self::extract($key) ?? $fail;
    }

    public static function __callStatic($kin, $lot = []) {
        return self::get($kin, array_shift($lot)) ?? parent::__callStatic($kin, $lot);
    }

}