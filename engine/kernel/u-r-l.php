<?php

class URL extends Genome {

    protected $s = null;
    protected $lot = [];

    public function __construct(string $in = null, string $self = '$') {
        $this->s = $self;
        if (isset($in)) {
            $url = parse_url($in);
            $url['path'] = trim($url['path'] ?? "", '/');
            $a = explode('/', $url['path']);
            if (is_numeric(end($a))) {
                $url['i'] = (int) array_pop($a);
                $url['path'] = implode('/', $a);
            } else {
                $url['i'] = null;
            }
            $url['clean'] = rtrim(strtr(preg_replace('#[?&\#].*$#', "", $in), [
                '<' => '%3C',
                '>' => '%3E',
                '&' => '%26',
                '"' => '%22'
            ]), '/');
            $url['$'] = rtrim($url['clean'] . '/' . $url['i'], '/');
            $q = $url['query'] ?? "";
            $url['query'] = (strpos($q, '?') !== 0 ? '?' : "") . str_replace('&amp;', '&', $q);
            $h = $url['fragment'] ?? "";
            $url['hash'] = (strpos($h, '#') !== 0 ? '#' : "") . $h;
            unset($url['fragment']);
            $this->lot = $url;
        } else {
            $this->lot = $GLOBALS['URL'];
        }
        parent::__construct();
    }

    // `$url->path('.')`
    public function path(string $separator = null) {
        $path = $this->lot['path'];
        return isset($separator) ? str_replace('/', $separator, $path) : $path;
    }

    // `$url->query('&amp;')`
    public function query($var = null) {
        $query = $this->lot['query'];
        if (isset($var)) {
            // `$url->query(';')`
            if (is_string($var)) {
                return str_replace('&', $var, $query);
            } else if (is_array($var)) {
                $var = extend(['?', '&', '=', ""], $var, false);
                return str_replace(['?', '&', '=', X], $var, $query . X);
            }
        }
        return $query;
    }

    // `$url->hash('#!')`
    public function hash(string $prefix = null) {
        $hash = $this->lot['hash'];
        return isset($prefix) ? $prefix . substr($hash, 1) : $hash;
    }

    public function __call(string $kin, array $lot = []) {
        return array_key_exists($kin, $this->lot) ? $this->lot[$kin] : null;
    }

    public function __set(string $key, $value = null) {
        $this->lot[$key] = $value;
    }

    // Fix case for `isset($url->key)` or `!empty($url->key)`
    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __unset(string $key) {
        unset($this->lot[$key]);
    }

    public function __toString() {
        return (string) $this->lot[$this->s] ?? "";
    }

    public static function long(string $path = "", $root = true) {
        $url = $GLOBALS['URL'];
        $b = false;
        if (strpos($path, '//') === 0) {
            return rtrim($url['scheme'] . ':' . $path, '/');
        } else if (strpos($path, '/') === 0) {
            $path = ltrim($path, '/');
            $b = true; // Relative to the root domain
        }
        if (
            strpos($path, '://') === false &&
            strpos($path, 'data:') !== 0 &&
            strpos($path, '?') !== 0 &&
            strpos($path, '&') !== 0 &&
            strpos($path, '#') !== 0 &&
            strpos($path, 'javascript:') !== 0
        ) {
            return trim(($root && $b ? $url['protocol'] . $url['host'] : $url['$']) . '/' . str_replace([X . '&', X], ['?', ""], X . ltrim($path, '/')), '/');
        }
        return str_replace([X . '&', X], ['?', ""], X . $path);
    }

    public static function short(string $path = "", $root = true) {
        $url = $GLOBALS['URL'];
        if (strpos($path, '//') === 0 && strpos($path, '//' . $url['host']) !== 0) {
            return $path; // Ignore external URL
        }
        return $root ? str_replace([
            X . $url['protocol'] . $url['host'],
            X . '//' . $url['host'],
            X
        ], "", X . $path) : ltrim(str_replace([
            X . $url['$'],
            X . '//' . rtrim($url['host'] . '/' . $url['directory'], '/'),
            X
        ], "", X . $path), '/');
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        return (new static)->__call($kin, $lot);
    }

}