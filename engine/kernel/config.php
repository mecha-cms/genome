<?php

class Config extends Genome {

    protected static $lot = [];
    protected static $a = [];

    public static function ignite(...$lot) {
        $c = static::class;
        if (!isset($lot[0])) {
            return (self::$lot[$c] = []);
        }
        $a = Is::file($lot[0]) ? require $lot[0] : $lot[0];
        return (self::$lot[$c] = self::$a[$c] = a($a));
    }

    public static function set($key, $value = null) {
        $c = static::class;
        $cargo = [];
        $key = a($key);
        $value = a($value);
        if (!is_array($key)) {
            Anemon::set($cargo, $key, $value);
        } else {
            $cargo = $key;
        }
        $o = (array) (self::$lot[$c] ?? []);
        self::$lot[$c] = extend($o, $cargo);
        return new static;
    }

    public static function get($key = null, $fail = false, $array = false) {
        $c = static::class;
        if (!isset($key)) {
            $out = !empty(self::$lot[$c]) ? self::$lot[$c] : $fail;
            return $array ? $out : o($out);
        } else if (is_array($key) || is_object($key)) {
            $out = [];
            foreach ($key as $k => $v) {
                $out[$k] = self::get($k, $v, $array);
            }
            // `get($keys = [], $array = false)`
            return $fail ? $out : o($out);
        }
        // `get($key = null, $fail = false, $array = false)`
        $out = (array) (self::$lot[$c] ?? []);
        $out = Anemon::get($out, $key, $fail);
        return $array ? $out : o($out);
    }

    public static function reset($key = null) {
        $c = static::class;
        if (isset($key)) {
            foreach ((array) $key as $value) {
                Anemon::reset(self::$lot[$c], $value);
            }
        } else {
            self::$lot[$c] = [];
        }
        return new static;
    }

    public static function alt(...$lot) {
        $c = static::class;
        self::set(...$lot);
        self::$lot[$c] = extend(self::$lot[$c], self::$a[$c]);
        return new static;
    }

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $fail = $alt = false;
        if (count($lot)) {
            $test = self::get($kin);
            // Asynchronous value with function closure
            if ($test instanceof \Closure) {
                return fn($test, $lot, $this);
            // Rich asynchronous value with class instance
            } else if ($fn = fn\is\instance($test)) {
                if (method_exists($fn, '__invoke')) {
                    return call_user_func([$fn, '__invoke'], ...$lot);
                }
            }
            // Else, static value
            $kin .= '.' . array_shift($lot);
            $fail = array_shift($lot) ?: false;
            $alt = array_shift($lot) ?: false;
        }
        if (is_callable($fail)) {
            return fn($fail, [self::get($kin, $alt)], $this);
        }
        return self::get($kin, $fail);
    }

    public function __set($key, $value = null) {
        return self::set($key, $value);
    }

    public function __get($key) {
        return self::get($key, null);
    }

    // Fix case for `isset($config->key)` or `!empty($config->key)`
    public function __isset($key) {
        return !!self::get($key);
    }

    public function __unset($key) {
        self::reset($key);
    }

    public function __toString() {
        return json_encode(self::get());
    }

    public function __invoke($fail = []) {
        return self::get(null, $fail, true);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $fail = $alt = false;
        if (count($lot)) {
            $test = self::get($kin);
            // Asynchronous value with function closure
            if ($test instanceof \Closure) {
                return fn($test, $lot);
            // Rich asynchronous value with class instance
            } else if ($fn = fn\is\instance($test)) {
                if (method_exists($fn, '__invoke')) {
                    return call_user_func([$fn, '__invoke'], ...$lot);
                }
            }
            // Else, static value
            $kin .= '.' . array_shift($lot);
            $fail = array_shift($lot) ?: false;
            $alt = array_shift($lot) ?: false;
        }
        if (is_callable($fail)) {
            return fn($fail, [self::get($kin, $alt)]);
        }
        return self::get($kin, $fail);
    }

}