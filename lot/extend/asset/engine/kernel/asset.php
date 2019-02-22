<?php

class Asset extends Genome {

    public static $lot = [];

    public static function URL(string $url) {
        $path = self::path($url);
        return isset($path) ? To::URL($path) : (strpos($url, '://') !== false || strpos($url, '//') === 0 ? $url : null);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if ($v = self::_('.' . $kin)) {
            $c = static::class;
            $path = array_shift($lot);
            $a = array_shift($lot) ?: [];
            $fn = $v[0];
            if (isset($path)) {
                $g = self::get($path, [
                    'path' => self::path($path),
                    'url' => self::URL($path),
                    'data' => [],
                    'stack' => null
                ]);
                $data = is_array($a) && is_array($g['data']) ? extend($a, $g['data']) : ($g['data'] ?: $a);
                return is_callable($fn) ? call_user_func($fn, $g, $path, $data) : ($g['path'] ? file_get_contents($g['path']) : "");
            }
            if (isset(self::$lot[$c][1][$kin])) {
                $assets = Anemon::eat(self::$lot[$c][1][$kin])->sort([1, 'stack'], true);
                $out = "";
                if (is_callable($fn)) {
                    foreach ($assets as $k => $v) {
                        $out .= call_user_func($fn, $v, $k, extend($a, $v['data'])) . N;
                    }
                } else {
                    foreach ($assets as $k => $v) {
                        if ($v['path'] !== false) {
                            $out .= file_get_contents($v['path']) . N;
                        }
                    }
                }
                return strlen(N) ? substr($out, 0, -strlen(N)) : $out;
            }
            return "";
        }
        return parent::__callStatic($kin, $lot);
    }

    public static function get(string $path = null, int $i = 1) {
        $c = static::class;
        if (isset($path)) {
            $x = Path::X($path);
            return self::$lot[$c][$i][$x][$path] ?? null;
        }
        return self::$lot[$c][$i] ?? [];
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
        $c = static::class;
        if (is_array($path)) {
            foreach ($path as $v) {
                self::reset($v);
            }
        } else if (isset($path)) {
            $x = Path::X($path);
            self::$lot[$c][0][$x][$path] = 1;
            unset(self::$lot[$c][1][$x][$path]);
        } else {
            self::$lot[$c] = [];
        }
    }

    public static function set($path, float $stack = null, array $data = []) {
        $i = 0;
        $stack = (array) $stack;
        $c = static::class;
        foreach ((array) $path as $k => $v) {
            $x = Path::X($v);
            if (!isset(self::$lot[$c][0][$x][$v])) {
                self::$lot[$c][1][$x][$v] = [
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