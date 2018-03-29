<?php

class Asset extends Genome {

    public static $lot = [];

    public static function path($path, $fail = false) {
        global $url;
        if (strpos($path, '://') !== false || strpos($path, '//') === 0) {
            // External URL, nothing to check!
            if (strpos($path, '://' . $url->host) === false && strpos($path, '//' . $url->host) !== 0) {
                return $fail;
            }
        }
        // Return the path if exist!
        return File::exist($path, $fail);
    }

    public static function URL($url, $fail = false) {
        $path = self::path($url, false);
        return $path !== false ? To::URL($path) : (strpos($url, '://') !== false || strpos($url, '//') === 0 ? $url : $fail);
    }

    public static function set($path, $stack = null) {
        $i = 0;
        $stack = (array) $stack;
        foreach ((array) $path as $k => $v) {
            $x = Path::X($v);
            if (!isset(self::$lot[$x][0][$v])) {
                self::$lot[$x][1][$v] = [
                    'path' => self::path($v),
                    'url' => self::URL($v),
                    'id' => $v,
                    'stack' => (float) (isset($stack[$k]) ? $stack[$k] : (end($stack) !== false ? end($stack) : 10) + $i)
                ];
                $i += .1;
            }
        }
        return new static;
    }

    public static function get($path = null, $fail = false) {
        if (isset($path)) {
            $x = Path::X($path);
            return isset(self::$lot[$x][1][$path]) ? self::$lot[$x][1][$path] : $fail;
        }
        return !empty(self::$lot) ? self::$lot : $fail;
    }

    public static function reset($path = null) {
        if (isset($path)) {
            $x = Path::X($path);
            self::$lot[$x][0][$path] = 1;
            unset(self::$lot[$x][1][$path]);
        } else {
            self::$lot = [];
        }
        return new static;
    }

    public static function __callStatic($kin, $lot = []) {
        $path = array_shift($lot);
        $attr = array_shift($lot) ?: [];
        if ($fn = self::_('.' . $kin)) {
            if (isset($path)) {
                $s = self::get($path, [
                    'path' => null,
                    'url' => null,
                    'id' => null,
                    'stack' => null
                ]);
                return is_callable($fn) ? call_user_func($fn, $s, $path, $attr) : ($s['path'] ? file_get_contents($s['path']) : "");
            }
            if (isset(self::$lot[$kin][1])) {
                $assets = Anemon::eat(self::$lot[$kin][1])->sort([1, 'stack'], true)->vomit();
                $output = "";
                if (is_callable($fn)) {
                    foreach ($assets as $k => $v) {
                        $output .= call_user_func($fn, $v, $k, $attr) . N;
                    }
                } else {
                    foreach ($assets as $k => $v) {
                        if ($v['path'] !== false) {
                            $output .= file_get_contents($v['path']) . N;
                        }
                    }
                }
                return strlen(N) ? substr($output, 0, -strlen(N)) : $output;
            }
        }
        return parent::__callStatic($kin, $lot);
    }

}