<?php

class Shield extends Extend {

    const config = [
        'x' => ['html', 'php'],
        'id' => 'document',
        'union' => ['html', "", ['class' => true]]
    ];

    public static $config = self::config;

    protected static $lot = [];

    public static function path($in, $fail = false) {
        $c = static::class;
        $out = [];
        if (is_string($in)) {
            // Full path, be quick!
            if (strpos($in, ROOT) === 0) {
                return File::exist($in, $fail);
            }
            $id = strtr($in, DS, '/');
            // Added by the `Shield::get()`
            if (!isset(self::$lot[$c][0][$id]) && isset(self::$lot[$c][1][$id])) {
                return File::exist(self::$lot[$c][1][$id], $fail);
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
        return File::exist($o, $fail);
    }

    public static function set($id, $path = null) {
        $c = static::class;
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                self::set($k, $v);
            }
        } else {
            if (!isset(self::$lot[$c][0][$id])) {
                $id = strtr($id, DS, '/');
                self::$lot[$c][1][$id] = is_callable($path) ? fn($path, [$id], null, $c) : $path;
            }
        }
        return new static;
    }

    public static function get($in, $fail = false, $print = true) {
        $NS = c2f(static::class, '_', '/');
        $out = "";
        Lot::set('lot', []);
        if (is_array($fail)) {
            Lot::set('lot', $fail);
            $fail = false;
        }
        if ($path = self::path($in, $fail)) {
            ob_start();
            extract(Lot::get(), EXTR_SKIP);
            require $path;
            $out = ob_get_clean();
            $c = static::class;
            // Begin shield
            Hook::fire($NS . '.enter', [$out, $in, $path], null, $c);
            $out = Hook::fire($NS . '.' . __FUNCTION__, [$out, $in, $path], null, $c);
            // End shield
            Hook::fire($NS . '.exit', [$out, $in, $path], null, $c);
        }
        if (!$print) {
            return $out;
        }
        echo $out;
    }

    public static function reset($id = null) {
        $c = static::class;
        if (!isset($id)) {
            self::$lot[$c] = [];
        } else if (is_array($id)) {
            foreach ($id as $v) {
                self::reset($v);
            }
        } else {
            $id = strtr($id, DS, '/');
            self::$lot[$c][0][$id] = 1;
            unset(self::$lot[$c][1][$id]);
        }
        return new static;
    }

    public static function attach($in, $fail = false) {
        if (!$out = self::get($in, $fail, false)) {
            $out = fail('<code>' . __METHOD__ . '(' . v(json_encode($in)) . ')</code>');
        }
        echo ($out = Hook::fire(c2f(static::class, '_', '/') . '.yield', [$out, $in]));
        return $out;
    }

    public static function abort($code = 404, $fail = false) {
        $i = is_string($code) ? explode('/', strtr($code, DS, '/'))[0] : '404';
        HTTP::status((int) $i);
        return Shield::attach($code, $fail);
    }

    public static function exist(string $id, $active = true) {
        $exist = is_dir(constant(u(static::class)) . DS . $id);
        return $active ? ($exist && static::$config['id'] === $id) : $exist;
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $kin = strtr($kin, '_', '-');
        // `self::fake('foo/bar', ['key' => 'value'])`
        if (count($lot)) {
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

}