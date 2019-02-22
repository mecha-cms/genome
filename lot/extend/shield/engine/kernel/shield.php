<?php

class Shield extends Extend {

    protected static $lot = [];
    public static $config = self::config;

    const config = [
        'x' => ['html', 'php'],
        'id' => 'document',
        'union' => ['html', "", ['class' => true]]
    ];

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

    public static function abort($code = 404) {
        $i = is_string($code) ? explode('/', strtr($code, DS, '/'))[0] : '404';
        HTTP::status((int) $i);
        return Shield::attach($code);
    }

    public static function attach($in) {
        if (null === ($out = self::get($in, [], false))) {
            ob_start();
            err('<code>' . __METHOD__ . '(' . v(json_encode($in)) . ')</code>');
            $out = ob_get_clean();
        }
        $out = Hook::fire(c2f(static::class, '_', '/') . '.yield', [$out, $in]);
        echo $out;
        return $out;
    }

    public static function exist(string $id, $active = true) {
        $exist = is_dir(constant(u(static::class)) . DS . $id);
        return $active ? ($exist && static::$config['id'] === $id) : $exist;
    }

    public static function get($in, array $lot = [], $print = true) {
        $out = null;
        $prefix = c2f(static::class, '_', '/');
        Lot::set('lot', $lot);
        if ($path = self::path($in)) {
            ob_start();
            extract(Lot::get(), EXTR_SKIP);
            require $path;
            $out = ob_get_clean();
            $c = static::class;
            // Begin shield
            Hook::fire($prefix . '.enter', [$out, $in, $path], null, $c);
            $out = Hook::fire($prefix . '.' . __FUNCTION__, [$out, $in, $path], null, $c);
            // End shield
            Hook::fire($prefix . '.exit', [$out, $in, $path], null, $c);
        }
        if (!$print) {
            return $out;
        }
        echo $out;
    }

    public static function path($in) {
        $c = static::class;
        $out = [];
        if (is_string($in)) {
            // Full path, be quick!
            if (strpos($in, ROOT) === 0) {
                return File::exist($in) ?: null;
            }
            $id = strtr($in, DS, '/');
            // Added by the `Shield::get()`
            if (isset(self::$lot[$c][1][$id]) && !isset(self::$lot[$c][0][$id])) {
                return File::exist(self::$lot[$c][1][$id]) ?: null;
            }
            // Guessing …
            $out = Anemon::step($id, '/');
            array_unshift($out, strtr($out[0], '/', '.'));
            $out = array_unique($out);
        } else {
            $out = $in;
        }
        $o = [];
        $folder = constant(u(static::class));
        $id = static::$config['id'];
        $extension = static::$config['x'];
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
                    $o[] = $folder . DS . $id . DS . $v . $vv;
                }
            } else {
                $o[] = $v;
            }
        }
        return File::exist($o) ?: null;
    }

    public static function reset($id = null) {
        $c = static::class;
        if (is_array($id)) {
            foreach ($id as $v) {
                self::reset($v);
            }
        } else if (isset($id)) {
            $id = strtr($id, DS, '/');
            self::$lot[$c][0][$id] = 1;
            unset(self::$lot[$c][1][$id]);
        } else {
            self::$lot[$c] = [];
        }
    }

    public static function set($id, string $path = null) {
        $c = static::class;
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                self::set($k, $v);
            }
        } else {
            if (!isset(self::$lot[$c][0][$id])) {
                $id = strtr($id, DS, '/');
                self::$lot[$c][1][$id] = $path;
            }
        }
    }

}