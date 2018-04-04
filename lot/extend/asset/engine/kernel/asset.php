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
        // Full path, be quick!
        if (strpos($path = To::path($path), ROOT) === 0) {
            return File::exist($path, $fail);
        }
        // Return the path relative to the `.\lot\asset` or `.` folder if exist!
        $s = ltrim($path, DS);
        return File::exist([ASSET . DS . $s, ROOT . DS . $s], $fail);
    }

    public static function URL($url, $fail = false) {
        $path = self::path($url, false);
        return $path !== false ? To::URL($path) : (strpos($url, '://') !== false || strpos($url, '//') === 0 ? $url : $fail);
    }

    public static function set($path, $stack = null) {
        $i = 0;
        $stack = (array) $stack;
        $c = static::class;
        foreach ((array) $path as $k => $v) {
            $x = Path::X($v);
            if (!isset(self::$lot[$c][0][$x][$v])) {
                self::$lot[$c][1][$x][$v] = [
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

    public static function get($path = null, $fail = false, $i = 1) {
        $c = static::class;
        if (isset($path)) {
            $x = Path::X($path);
            return isset(self::$lot[$c][$i][$x][$path]) ? self::$lot[$c][$i][$x][$path] : $fail;
        }
        return !empty(self::$lot[$c][$i]) ? self::$lot[$c][$i] : $fail;
    }

    public static function reset($path = null) {
        $c = static::class;
        if (!isset($path)) {
            self::$lot[$c] = [];
        } else if (is_array($path)) {
            foreach ($path as $v) {
                self::reset($v);
            }
        } else {
            $x = Path::X($path);
            self::$lot[$c][0][$x][$path] = 1;
            unset(self::$lot[$c][1][$x][$path]);
        }
        return new static;
    }

    public static function __callStatic($kin, $lot = []) {
        $path = array_shift($lot);
        $a = array_shift($lot) ?: [];
        $c = static::class;
        if ($fn = self::_('.' . $kin)) {
            if (isset($path)) {
                $s = self::get($path, [
                    'path' => null,
                    'url' => null,
                    'id' => null,
                    'stack' => null
                ]);
                return is_callable($fn) ? call_user_func($fn, $s, $path, $a) : ($s['path'] ? file_get_contents($s['path']) : "");
            }
            if (isset(self::$lot[$c][1][$kin])) {
                $assets = Anemon::eat(self::$lot[$c][1][$kin])->sort([1, 'stack'], true)->vomit();
                $output = "";
                if (is_callable($fn)) {
                    foreach ($assets as $k => $v) {
                        $output .= call_user_func($fn, $v, $k, $a) . N;
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
            return "";
        }
        return parent::__callStatic($kin, $lot);
    }

}