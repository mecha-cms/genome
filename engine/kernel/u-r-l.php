<?php

final class URL extends Genome implements \ArrayAccess {

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
            if (strpos($in, '://') === false) {
                $out = array_replace($GLOBALS['URL'], $out);
                $out['ground'] = $out['protocol'] . $out['host'];
                $out['root'] = $out['ground'] . $out['directory'];
                $out['clean'] = rtrim($out['root'] . $out['path'], '/');
                if (strpos($in, '/') === 0 && $d = $out['directory']) {
                    $out['directory'] = null;
                    $out['root'] = $out['ground'];
                    $out['clean'] = rtrim($out['root'] . $out['path'], '/');
                }
            } else if (strpos($in, $GLOBALS['URL']['root']) === 0) {
                // TODO
                // $out = array_replace($out, $GLOBALS['URL']);
            } else {
                $out['protocol'] = $out['scheme'] . '://';
                $out['clean'] = preg_split('/[?&#]/', $in)[0];
            }
            $path = trim($out['path'] ?? "", '/');
            $a = explode('/', $path);
            if (is_numeric(end($a))) {
                $out['i'] = (int) array_pop($a);
                $path = implode('/', $a);
            } else {
                $out['i'] = null;
            }
            $out['path'] = $path !== "" ? '/' . $path : null;
            $out['clean'] = strtr($out['clean'], [
                '<' => '%3C',
                '>' => '%3E',
                '&' => '%26',
                '"' => '%22'
            ]);
            $q = $out['query'] ?? "";
            $h = $out['fragment'] ?? "";
            $out['query'] = $q !== "" ? '?' . str_replace('&amp;', '&', $q) : null;
            $out['hash'] = $h !== "" ? '#' . $h : null;
            unset($out['fragment']);
            $this->lot = $out;
        } else {
            $this->lot = $GLOBALS['URL'];
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

    public function offsetExists($i) {
        return isset($this->lot[$i]);
    }

    public function offsetGet($i) {
        return $this->lot[$i] ?? null;
    }

    public function offsetSet($i, $value) {
        if (isset($i)) {
            $this->lot[$i] = $value;
        } else {
            $this->lot[] = $value;
        }
    }

    public function offsetUnset($i) {
        unset($this->lot[$i]);
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