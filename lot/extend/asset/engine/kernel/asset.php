<?php

final class Asset extends Genome {

    public static $lot = [];

    public static function URL(string $url) {
        $path = self::path($url);
        return isset($path) ? To::URL($path) : (strpos($url, '://') !== false || strpos($url, '//') === 0 ? $url : null);
    }

    public static function get(string $path = null, int $i = 1) {
        if (isset($path)) {
            $x = Path::X($path);
            return self::$lot[$i][$x][$path] ?? null;
        }
        return self::$lot[$i] ?? [];
    }

    public static function join(string $x, string $separator = "") {
        if ($v = self::_('.' . $x)) {
            $fn = $v[0];
            if (isset(self::$lot[1][$x])) {
                $assets = Anemon::eat(self::$lot[1][$x])->sort([1, 'stack'], true);
                $out = [];
                if (is_callable($fn)) {
                    foreach ($assets as $k => $v) {
                        $out[] = call_user_func($fn, $v, $k);
                    }
                } else {
                    foreach ($assets as $k => $v) {
                        if (isset($v['path']) && is_file($v['path'])) {
                            $out[] = file_get_contents($v['path']);
                        }
                    }
                }
                return implode($separator, $out);
            }
        }
        return "";
    }

    public static function path(string $path) {
        if (strpos($path, '://') !== false || strpos($path, '//') === 0) {
            // External URL, nothing to check!
            $host = $GLOBALS['URL']['host'] ?? "";
            if (strpos($path, '://' . $host) === false && strpos($path, '//' . $host) !== 0) {
                return null;
            }
        }
        // Full path, be quick!
        if (strpos($path = strtr($path, '/', DS), ROOT) === 0) {
            return File::exist($path) ?: null;
        }
        // Return the path relative to the `.\lot\asset` or `.` folder if exist!
        $s = ltrim($path, DS);
        return File::exist([
            constant(u(static::class)) . DS . $s,
            ROOT . DS . $s
        ]) ?: null;
    }

    public static function reset($path = null) {
        if (is_array($path)) {
            foreach ($path as $v) {
                self::reset($v);
            }
        } else if (isset($path)) {
            $x = Path::X($path);
            self::$lot[0][$x][$path] = 1;
            unset(self::$lot[1][$x][$path]);
        } else {
            self::$lot = [];
        }
    }

    public static function set($path, float $stack = null, array $data = []) {
        $i = 0;
        $stack = (array) $stack;
        foreach ((array) $path as $k => $v) {
            $x = Path::X($v);
            if (!isset(self::$lot[0][$x][$v])) {
                self::$lot[1][$x][$v] = [
                    'path' => self::path($v),
                    'url' => self::URL($v),
                    'data' => $data,
                    'stack' => (float) ($stack[$k] ?? (end($stack) !== false ? end($stack) : 10) + $i)
                ];
                $i += .1;
            }
        }
    }

}