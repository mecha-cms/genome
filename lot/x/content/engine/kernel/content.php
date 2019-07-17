<?php

class Content extends Genome {

    protected static $lot;

    const config = [
        'root' => ROOT,
        'x' => ['html', 'php']
    ];

    public static $config = self::config;

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $kin = strtr($kin, '_', '-');
        // `self::fake('foo/bar', ['key' => 'value'])`
        if ($lot) {
            // `self::fake(['key' => 'value'])`
            if (is_array($lot[0])) {
                // → is equal to `self::fake("", ['key' => 'value'])`
                array_unshift($lot, "");
            }
            $kin = trim($kin . '/' . array_shift($lot), '/');
            $lot = array_replace([[], true], $lot);
        }
        return self::get($kin, ...$lot);
    }

    public static function get($in, array $lot = [], $print = true) {
        if ($path = self::path($in)) {
            extract(array_replace($GLOBALS, $lot), EXTR_SKIP);
            if ($print) {
                require $path;
            } else {
                ob_start();
                require $path;
                return ob_get_clean();
            }
        }
        return null;
    }

    public static function path($in) {
        $out = [];
        $c = static::class;
        $folder = static::$config['root'];
        $extension = static::$config['x'];
        if (is_string($in)) {
            // Full path, be quick!
            if (strpos($in, ROOT) === 0 && is_file($in)) {
                return $in;
            }
            $id = strtr($in, DS, '/');
            // Added by the `Content::get()`
            if (isset(self::$lot[$c][1][$id]) && !isset(self::$lot[$c][0][$id])) {
                return File::exist(self::$lot[$c][1][$id]) ?: null;
            }
            // Guessing…
            $out = step($id, '/');
            array_unshift($out, strtr($out[0], '/', '.'));
            $out = array_unique($out);
        } else {
            $out = $in;
        }
        $any = [];
        foreach ((array) $out as $v) {
            $v = strtr($v, '/', DS);
            if (strpos($v, $folder) !== 0) {
                foreach ($extension as $x) {
                    $vv = "";
                    $xx = pathinfo($v, PATHINFO_EXTENSION);
                    if (!$xx) {
                        $vv = '.' . $x;
                    } else if ($xx !== $x) {
                        continue;
                    }
                    $any[] = $folder . DS . $v . $vv;
                }
            } else {
                $any[] = $v;
            }
        }
        return File::exist($any) ?: null;
    }

    public static function let($id = null) {
        if (is_array($id)) {
            foreach ($id as $v) {
                self::let($v);
            }
        } else if (isset($id)) {
            $id = strtr($id, DS, '/');
            $c = static::class;
            self::$lot[$c][0][$id] = 1;
            unset(self::$lot[$c][1][$id]);
        } else {
            self::$lot[$c] = [];
        }
    }

    public static function set($id, string $path = null) {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                self::set($k, $v);
            }
        } else {
            $c = static::class;
            if (!isset(self::$lot[$c][0][$id])) {
                $id = strtr($id, DS, '/');
                self::$lot[$c][1][$id] = $path;
            }
        }
    }

}