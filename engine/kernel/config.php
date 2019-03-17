<?php

class Config extends Genome {

    protected static $a = [];
    protected static $lot = [];

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        if ($lot) {
            $test = self::get($kin);
            // Asynchronous value with function closure
            if ($test instanceof \Closure) {
                return fn($test, $lot, $this, static::class);
            // Rich asynchronous value with class instance
            } else if ($fn = fn\is\instance($test)) {
                if (method_exists($fn, '__invoke')) {
                    return call_user_func([$fn, '__invoke'], ...$lot);
                }
            }
            // Else, static value
            $kin .= '.' . array_shift($lot);
            $array = array_shift($lot) ?: false;
        }
        return self::get($kin);
    }

    public function __get(string $key) {
        if (method_exists($this, $key)) {
            if ((new \ReflectionMethod($this, $key))->isPublic()) {
                return $this->{$key}();
            }
        }
        if (self::_($key)) {
            return $this->__call($key);
        }
        return self::get($key);
    }

    public function __invoke() {
        return (array) self::get(null, true);
    }

    // Fix case for `isset($config->key)` or `!empty($config->key)`
    public function __isset(string $key) {
        return !!self::get($key);
    }

    public function __set(string $key, $value = null) {
        return self::set($key, $value);
    }

    public function __toString() {
        return json_encode(self::get());
    }

    public function __unset(string $key) {
        self::reset($key);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        if ($lot) {
            $test = self::get($kin);
            // Asynchronous value with function closure
            if ($test instanceof \Closure) {
                return fn($test, $lot, null, static::class);
            // Rich asynchronous value with class instance
            } else if ($fn = fn\is\instance($test)) {
                if (method_exists($fn, '__invoke')) {
                    return call_user_func([$fn, '__invoke'], ...$lot);
                }
            }
            // Else, static value
            $kin .= '.' . array_shift($lot);
            $array = array_shift($lot) ?: false;
        }
        return self::get($kin);
    }

    public static function alt(...$lot) {
        $c = static::class;
        self::set(...$lot);
        self::$lot[$c] = array_replace_recursive(self::$lot[$c], self::$a[$c]);
    }

    public static function get($key = null, $array = false) {
        $c = static::class;
        if (is_array($key)) {
            $out = [];
            foreach ($key as $k => $v) {
                $out[$k] = self::get($k, $array) ?? $v;
            }
            return $array ? $out : o($out);
        } else if (isset($key)) {
            $out = self::$lot[$c] ?? [];
            $out = Anemon::get($out, $key);
            return $array ? $out : o($out);
        }
        $out = self::$lot[$c] ?? [];
        return $array ? $out : o($out);
    }

    public static function load(...$lot) {
        $c = static::class;
        if (isset($lot[0])) {
            $a = Is::file($lot[0]) ? require $lot[0] : $lot[0];
            return (self::$lot[$c] = self::$a[$c] = a($a));
        }
        return (self::$lot[$c] = []);
    }

    public static function reset($key = null) {
        $c = static::class;
        if (is_array($key)) {
            foreach ($key as $v) {
                self::reset($v);
            }
        } else if (isset($key)) {
            Anemon::reset(self::$lot[$c], $key);
        } else {
            self::$lot[$c] = [];
        }
    }

    public static function set($key, $value = null) {
        $c = static::class;
        $in = [];
        if (is_array($key)) {
            $in = $key;
        } else {
            Anemon::set($in, $key, $value);
        }
        $out = self::$lot[$c] ?? [];
        self::$lot[$c] = array_replace_recursive($out, $in);
    }

}