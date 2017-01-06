<?php

class Asset extends Genome {

    public static $lot = [];

    public static function path($path, $fail = false) {
        extract(Lot::get(null, []));
        if (strpos($path, '://') !== false || strpos($path, '//') === 0) {
            // External URL, nothing to check …
            if (strpos($path, '://' . $url->host) === false && strpos($path, '//' . $url->host) !== 0) {
                return $fail;
            }
        }
        // Full path, be quick!
        if (strpos($path, ROOT) === 0) {
            return File::exist($path, $fail);
        }
        return File::exist([
            // Relative to `asset` folder of the current shield
            SHIELD . DS . $config->shield . DS . 'asset' . DS . ltrim($path, '/'),
            // Relative to `lot\asset` folder
            ASSET . DS . ltrim($path, '/')
        ], $fail);
    }

    public static function url($path, $fail = false) {
        $path = self::path($path, false);
        return $path !== false ? To::url($path) : (strpos($path, '://') !== false || strpos($path, '//') === 0 ? $path : $fail);
    }

    public static function set($path, $stack = null) {
        $i = 0;
        $x = Path::X($path);
        $stack = (array) $stack;
        foreach ((array) $path as $k => $v) {
            if (!isset(self::$lot[$x][0][$v])) {
                self::$lot[$x][1][$v] = [
                    'path' => self::path($v),
                    'url' => self::url($v),
                    'id' => $v,
                    'stack' => (float) (isset($stack[$k]) ? $stack[$k] : (end($stack) !== null ? end($stack) : 10) + $i)
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

    public static function __callStatic($kin, $lot) {
        $path = array_shift($lot);
        $attr = array_shift($lot) ?: [];
        $fn = static::class . '\\Union::' . $kin;
        if (is_callable($fn) && !isset(self::$lot[$kin][1])) {
            self::$lot[$kin][1] = [];
        }
        if (isset($path)) {
            $s = self::$lot[$kin][1][$path];
            if (!isset($s)) {
                self::set($kin, $path);
            }
            return is_callable($fn) ? call_user_func($fn, $s, $path, $attr, $stack) : ($s['path'] ? file_get_contents($s['path']) : "");
        }
        if (isset(self::$lot[$kin][1])) {
            $assets = Anemon::eat(self::$lot[$kin][1])->sort(1, 'stack', true)->vomit();
            $html = "";
            if (is_callable($fn)) {
                foreach ($assets as $k => $v) {
                    $html .= call_user_func($fn, $v, $k, $attr) . N;
                }
            } else {
                foreach ($assets as $k => $v) {
                    if ($v['path'] !== false) {
                        $html .= file_get_contents($v['path']) . N;
                    }
                }
            }
            return strlen(N) ? substr($html, 0, -strlen(N)) : $html;
        }
        return parent::__callStatic($kin, $lot);
    }

}