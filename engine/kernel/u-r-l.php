<?php

final class URL extends Genome {

    private $lot;

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        if (method_exists($this, $m = '_' . $kin . '_') && (new \ReflectionMethod($this, $m))->isProtected()) {
            return $this->{$m}(...$lot);
        }
        if (isset($this->lot[$kin])) {
            return $this->lot[$kin] === "" ? null : $this->lot[$kin];
        }
        return null;
    }

    public function __construct(string $in = null) {
        if (isset($in)) {
            $out = parse_url($in);
            $path = trim($out['path'] ?? "", '/');
            $a = explode('/', $path);
            if (is_numeric(end($a))) {
                $out['i'] = (int) array_pop($a);
                $path = implode('/', $a);
            } else {
                $out['i'] = null;
            }
            $out['path'] = $path !== "" ? '/' . $path : null;
            $out['clean'] = rtrim(strtr(preg_split('/[?&#]/', $in)[0], [
                '<' => '%3C',
                '>' => '%3E',
                '&' => '%26',
                '"' => '%22'
            ]), '/');
            $out['$'] = rtrim($out['clean'] . '/' . $out['i'], '/');
            $q = $out['query'] ?? "";
            $h = $out['fragment'] ?? "";
            $out['query'] = $q !== "" ? '?' . str_replace('&amp;', '&', $q) : null;
            $out['hash'] = $h !== "" ? '#' . $h : null;
            unset($out['fragment']);
            $this->lot = $out;
        } else {
            $this->lot = $GLOBALS['URL'];
        }
        parent::__construct();
    }

    // Fix case for `isset($url->key)` or `!empty($url->key)`
    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __set(string $key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __toString() {
        return (string) ($this->lot['$'] ?? "");
    }

    public function __unset(string $key) {
        unset($this->lot[$key]);
    }

    // `$url->hash('#!')`
    protected function _hash_(string $prefix = '#') {
        $hash = $this->lot['hash'];
        return $hash ? $prefix . substr($hash, 1) : null;
    }

    // `$url->path('.')`
    protected function _path_(string $separator = '/', array $p = []) {
        $path = $this->lot['path'];
        if (!empty($p)) {
            $path = array_replace(explode('/', $path), $p);
            $path = implode($separator, $path);
        } else {
            $path = str_replace('/', $separator, $path);
        }
        return $path === "" ? null : $path;
    }

    // `$url->query('&amp;')`
    protected function _query_(string $separator = '&', array $q = []) {
        $query = From::query($this->lot['query'] ?? "");
        $query = array_replace_recursive($query, $q);
        return !empty($query) ? strtr(To::query($query), ['&' => $separator]) : null;
    }

    public static function long(string $path, $root = true) {
        $u = new static;
        $b = false;
        // `URL::long('//example.com')`
        if (strpos($path, '//') === 0) {
            return rtrim($u->scheme . ':' . $path, '/');
        // `URL::long('/foo/bar/baz/qux')`
        } else if (strpos($path, '/') === 0) {
            $path = ltrim($path, '/');
            $b = true; // Relative to the root domain
        }
        // `URL::long('&foo=bar&baz=qux')`
        $a = explode('?', $path, 2);
        if (count($a) === 1 && strpos($a[0], '&') !== false) {
            $a = explode('&', strtr($a[0], ['&amp;' => '&']), 2);
            $path = implode('?', $a);
        }
        if (
            strpos($path, '://') === false &&
            strpos($path, 'data:') !== 0 &&
            strpos($path, 'javascript:') !== 0 &&
            strpos($path, '?') !== 0 &&
            strpos($path, '&') !== 0 &&
            strpos($path, '#') !== 0
        ) {
            $r = $root && $b ? $u->scheme . '://' . $u->host : $u;
            return trim($r . '/' . $path, '/');
        }
        return $path;
    }

    public static function short(string $path = "", $root = true) {
        $u = new static;
        if (strpos($path, '//') === 0 && strpos($path, '//' . $u->host) !== 0) {
            return $path; // Ignore external URL
        }
        return $root ? str_replace([
            P . $u->scheme . '://' . $u->host,
            P . '//' . $u->host,
            P
        ], "", P . $path) : ltrim(str_replace([
            P . $u->scheme . ':',
            P . '//' . $u->host . $u->directory
        ], "", P . $path), '/');
    }

}