<?php

final class URL extends Genome {

    private $lot;

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        if (array_key_exists($kin, $this->lot)) {
            return $this->lot[$kin] === "" ? null : $this->lot[$kin];
        }
        return null;
    }

    public function __construct($in = null) {
        $this->lot = [
            'clean' => null,
            'current' => null,
            'directory' => null,
            'ground' => null,
            'hash' => null,
            'host' => null,
            'i' => null,
            'path' => null,
            'port' => null,
            'protocol' => null,
            'query' => null,
            'root' => null,
            'scheme' => null
        ];
        if (is_string($in)) {
            $out = parse_url($in);
            $out['protocol'] = $out['scheme'] . '://';
            if (isset($out['port'])) {
                $out['port'] = (int) $out['port'];
            }
            if (isset($out['path'])) {
                $out['path'] = $out['path'] !== "" ? $out['path'] : null;
            }
            if (isset($out['query'])) {
                $out['query'] = $out['query'] !== "" ? '?' . $out['query'] : null;
            }
            if (isset($out['fragment'])) {
                $out['hash'] = $out['fragment'] !== "" ? '#' . $out['fragment'] : null;
                unset($out['fragment']);
            }
            $out['ground'] = $out['root'] = $out['protocol'] . $out['host'];
            $this->lot = array_replace($this->lot, $out);
        } else if (is_array($in)) {
            $this->lot = array_replace($this->lot, $in);
        }
    }

    public function __get(string $key) {
        return $this->__call($key);
    }

    // Fix case for `isset($url->key)` or `!empty($url->key)`
    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __set(string $key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __toString() {
        return (string) ($this->lot['root'] ?? "");
    }

    public function __unset(string $key) {
        unset($this->lot[$key]);
    }

    // `$url->hash('#!')`
    public function hash(string $prefix = '#') {
        $hash = $this->lot['hash'];
        return $hash ? $prefix . substr($hash, 1) : null;
    }

    // `$url->path('.')`
    public function path(string $separator = '/', array $p = []) {
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
    public function query(string $separator = '&', array $q = []) {
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
            $r = $u->{$root && $b ? 'ground' : 'root'};
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
            P . $u->ground,
            P . '//' . $u->host,
            P
        ], "", P . $path) : ltrim(str_replace([
            P . $u->scheme . ':',
            P . '//' . $u->host . $u->directory
        ], "", P . $path), '/');
    }

}