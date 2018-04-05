<?php

class URL extends Genome {

    protected $lot = [];

    public static function long($url, $root = true) {
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
            return trim(($root && $b ? $u['protocol'] . $u['host'] : $u['$']) . '/' . self::__($url), '/');
        }
        return self::__($url);
    }

    public static function short($url, $root = true) {
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

    protected static function __($s) {
        return str_replace(['\\', '/?', '/&', '/#'], ['/', '?', '&', '#'], $s);
    }

    public function __construct($input = null) {
        if (isset($input)) {
            $u = parse_url($input);
            if (isset($u['path'])) {
                $u['path'] = trim($u['path'], '/');
            }
            if (isset($u['query'])) {
                $u['query'] = (strpos($u['query'], '?') !== 0 ? '?' : "") . str_replace('&amp;', '&', $u['query']);
            }
            if (isset($u['fragment'])) {
                $u['hash'] = (strpos($u['fragment'], '#') !== 0 ? '#' : "") . $u['fragment'];
            }
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
        return $this->lot['$'];
    }

    public static function __callStatic($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        return (new static)->__call($kin, $lot);
    }

}