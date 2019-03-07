<?php

final class Shield extends Extend {

    protected static $lot = [];
    public static $config = self::config;

    const config = [
        'x' => ['html', 'php'],
        'id' => 'document',
        'root' => ['html', "", ['class' => true]]
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
        return self::attach($code);
    }

    public static function attach($in) {
        Hook::fire('enter', [null, $in]);
        if (null === ($out = self::get($in, [], false))) {
            throw new \InvalidArgumentException('Folder ' . static::$config['id'] . ' does not exist.');
        }
        $out = Hook::fire('content', [$out, $in]);
        Hook::fire('exit', [$out, $in]);
        return $out;
    }

    public static function exist(string $id, $active = true) {
        $exist = is_dir(constant(u(static::class)) . DS . $id);
        return $active ? ($exist && static::$config['id'] === $id) : $exist;
    }

    public static function get($in, array $lot = [], $print = true) {
        $out = null;
        Lot::set('lot', $lot);
        if ($path = self::path($in)) {
            ob_start();
            extract(Lot::get(), EXTR_SKIP);
            require $path;
            $out = ob_get_clean();
        }
        if (!$print) {
            return $out;
        }
        echo $out;
    }

    public static function path($in) {
        $out = [];
        if (is_string($in)) {
            // Full path, be quick!
            if (strpos($in, ROOT) === 0) {
                return File::exist($in) ?: null;
            }
            $id = strtr($in, DS, '/');
            // Added by the `Shield::get()`
            if (isset(self::$lot[1][$id]) && !isset(self::$lot[0][$id])) {
                return File::exist(self::$lot[1][$id]) ?: null;
            }
            // Guessing…
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
        if (is_array($id)) {
            foreach ($id as $v) {
                self::reset($v);
            }
        } else if (isset($id)) {
            $id = strtr($id, DS, '/');
            self::$lot[0][$id] = 1;
            unset(self::$lot[1][$id]);
        } else {
            self::$lot = [];
        }
    }

    public static function set($id, string $path = null) {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                self::set($k, $v);
            }
        } else {
            if (!isset(self::$lot[0][$id])) {
                $id = strtr($id, DS, '/');
                self::$lot[1][$id] = $path;
            }
        }
    }

}