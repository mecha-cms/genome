<?php

final class URL extends Genome {

    private $lot;

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
        $query = From::query($this->lot['query']);
        $query = array_replace_recursive($query, $q);
        $query = strtr(To::query($query), ['&' => $separator]);
        return $query === "" ? null : $query;
    }

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
            $out['path'] = trim($out['path'] ?? "", '/');
            $a = explode('/', $out['path']);
            if (is_numeric(end($a))) {
                $out['i'] = (int) array_pop($a);
                $out['path'] = implode('/', $a);
            } else {
                $out['i'] = null;
            }
            $out['clean'] = rtrim(strtr(preg_split('/[?&#]/', $in)[0], [
                '<' => '%3C',
                '>' => '%3E',
                '&' => '%26',
                '"' => '%22'
            ]), '/');
            $out['$'] = rtrim($out['clean'] . '/' . $out['i'], '/');
            $q = $out['query'] ?? "";
            $out['query'] = (strpos($q, '?') !== 0 ? '?' : "") . str_replace('&amp;', '&', $q);
            $h = $out['fragment'] ?? "";
            $out['hash'] = (strpos($h, '#') !== 0 ? '#' : "") . $h;
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

}