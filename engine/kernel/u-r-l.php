<?php

class URL extends Genome {

    protected $s = null;
    protected $lot = [];

    public static function long(string $url = "", $root = true) {
        $u = $GLOBALS['URL'];
        $b = false;
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $url = ltrim($url, '/');
            $b = true; // Relative to the root domain
        }
        if (
            strpos($url, '://') === false &&
            strpos($url, '//') !== 0 &&
            strpos($url, '?') !== 0 &&
            strpos($url, '&') !== 0 &&
            strpos($url, '#') !== 0 &&
            strpos($url, 'javascript:') !== 0
        ) {
            return trim(($root && $b ? $u['protocol'] . $u['host'] : $u['$']) . '/' . self::v($url), '/');
        }
        return self::v($url);
    }

    public static function short(string $url = "", $root = true) {
        $u = $GLOBALS['URL'];
        if (strpos($url, '//') === 0 && strpos($url, '//' . $u['host']) !== 0) {
            return $url; // Ignore external URL
        }
        $url = X . $url;
        if ($root) {
            return str_replace([X . $u['protocol'] . $u['host'], X . '//' . $u['host'], X], "", $url);
        }
        return ltrim(str_replace([X . $u['$'], X . '//' . rtrim($u['host'] . '/' . $u['directory'], '/'), X], "", $url), '/');
    }

    protected static function v(string $path = "") {
        return str_replace(['\\', '/?', '/&', '/#'], ['/', '?', '&', '#'], $path);
    }

    public function __construct($input = null, string $self = '$') {
        $this->s = $self;
        if (isset($input)) {
            $u = extend([
                '$' => null,
                'fragment' => "",
                'host' => "",
                'i' => null,
                'pass' => null,
                'path' => "",
                'port' => null,
                'query' => "",
                'scheme' => "",
                'user' => null
            ], parse_url($input));
            $u['path'] = trim($u['path'], '/');
            $a = explode('/', $u['path']);
            if (is_numeric(end($a))) {
                $u['i'] = (int) array_pop($a);
                $u['path'] = implode('/', $a);
            }
            $u['clean'] = rtrim(strtr(preg_replace('#[?&\#].*$#', "", $input), [
                '<' => '%3C',
                '>' => '%3E',
                '&' => '%26',
                '"' => '%22'
            ]), '/');
            $u['$'] = rtrim($u['clean'] . '/' . $u['i'], '/');
            $u['query'] = ($u['query'] && strpos($u['query'], '?') !== 0 ? '?' : "") . str_replace('&amp;', '&', $u['query']);
            $u['hash'] = ($u['fragment'] && strpos($u['fragment'], '#') !== 0 ? '#' : "") . $u['fragment'];
            unset($u['fragment']);
            $this->lot = $u;
        } else {
            $this->lot = $GLOBALS['URL'];
        }
        parent::__construct();
    }

    public function __call($kin, $lot = []) {
        $u = $this->lot;
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        } else if ($kin === 'path' && isset($u[$kin]) && isset($lot[0])) {
            return str_replace('/', $lot[0], $u[$kin]);
        } else if ($kin === 'query' && isset($u[$kin]) && $lot) {
            $a = array_shift($lot);
            if (is_string($a)) {
                $a = ['?', $a, '=', ""];
            }
            return str_replace(['?', '&', '=', X], $a, $u[$kin] . X);
        } else if ($kin === 'hash' && isset($u[$kin]) && isset($lot[0])) {
            return str_replace('#', $lot[0], $u[$kin]);
        }
        return array_key_exists($kin, $u) ? $u[$kin] : array_shift($lot);
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return $this->__call($key);
    }

    // Fix case for `isset($url->key)` or `!empty($url->key)`
    public function __isset($key) {
        return !!$this->__get($key);
    }

    public function __unset($key) {
        unset($this->lot[$key]);
    }

    public function __toString() {
        return (string) $this->lot[$this->s] ?? "";
    }

    public static function __callStatic($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        return (new static)->__call($kin, $lot);
    }

}