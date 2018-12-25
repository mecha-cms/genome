<?php

class Shield extends Genome {

    protected static $lot = [];

    public static function path($in, $fail = false) {
        $NS = c2f($c = static::class, '_', '/') . '.' . __FUNCTION__;
        $out = [];
        if (is_string($in)) {
            if (strpos($in, ROOT) !== 0) {
                $id = strtr($in, DS, '/');
                if (!isset(self::$lot[$c][0][$id]) && isset(self::$lot[$c][1][$id])) {
                    $out = self::$lot[$c][1][$id];
                } else {
                    $out = Anemon::step($id, '/');
                    array_unshift($out, strtr($out[0], '/', '.'));
                    $out = array_unique($out);
                }
            } else {
                $out = $in;
            }
        } else {
            $out = $in;
        }
        foreach ((array) $out as $k => $v) {
            $id = $v;
            $v = strtr($v, '/', DS);
            if (strpos($v, SHIELD) !== 0) {
                if (substr($v, -4) !== '.php') {
                    $v .= '.php';
                }
                $v = SHIELD . DS . Config::get('shield') . DS . $v;
            }
            $out[$k] = $v;
        }
        return File::exist(Hook::fire($NS, [$out, $in]), $fail);
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
            // Begin shield
            Hook::fire($NS . '.enter', [$out, $in, $path]);
            $out = Hook::fire($NS . '.' . __FUNCTION__, [$out, $in, $path]);
            // End shield
            Hook::fire($NS . '.exit', [$out, $in, $path]);
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

    public static function __callStatic($kin, array $lot = []) {
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