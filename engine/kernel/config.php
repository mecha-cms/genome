<?php

class Config extends Genome {

    protected static $bucket = [];
    protected static $a = [];

    public static function ignite(...$lot) {
        $c = static::class;
        if (!isset($lot[0])) {
            return (self::$bucket[$c] = []);
        }
        $a = is_string($lot[0]) && is_file($lot[0]) ? require $lot[0] : $lot[0];
        return (self::$bucket[$c] = self::$a[$c] = (array) a($a));
    }

    public static function set($key, $value = null) {
        $c = static::class;
        if (__is_anemon__($key)) $key = a($key);
        if (__is_anemon__($value)) $value = a($value);
        $cargo = [];
        if (!is_array($key)) {
            Anemon::set($cargo, $key, $value);
        } else {
            foreach ($key as $k => $v) {
                Anemon::set($cargo, $k, $v);
            }
        }
        self::$bucket[$c] = array_replace_recursive(self::$bucket[$c], $cargo);
        return new static;
    }

    public static function get($key = null, $fail = false) {
        $c = static::class;
        if (!isset($key)) {
            return !empty(self::$bucket[$c]) ? o(self::$bucket[$c]) : $fail;
        }
        if (__is_anemon__($key)) {
            $output = [];
            foreach ($key as $k => $v) {
                $output[$k] = self::get($k, $v);
            }
            return o($output);
        }
        return o(Anemon::get(self::$bucket[$c], $key, $fail));
    }

    public static function reset($key = null) {
        $c = static::class;
        if (isset($key)) {
            foreach ((array) $key as $value) {
                Anemon::reset(self::$bucket[$c], $value);
            }
        } else {
            self::$bucket[$c] = [];
        }
        return new static;
    }

    public static function alt(...$lot) {
        $c = static::class;
        self::set(...$lot);
        self::$bucket[$c] = array_replace_recursive(self::$bucket[$c], self::$a[$c]);
        return new static;
    }

    public function __call($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $fail = $alt = false;
        if (count($lot)) {
            $kin .= '.' . array_shift($lot);
            $fail = array_shift($lot) ?: false;
            $alt = array_shift($lot) ?: false;
        }
        if ($fail instanceof \Closure) {
            return call_user_func(\Closure::bind($fail, $this), self::get($kin, $alt));
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
        return self::get(null, o($fail));
    }

    public static function __callStatic($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $fail = $alt = false;
        if (count($lot)) {
            $kin .= '.' . array_shift($lot);
            $fail = array_shift($lot) ?: false;
            $alt = array_shift($lot) ?: false;
        }
        if ($fail instanceof \Closure) {
            return call_user_func($fail, self::get($kin, $alt));
        }
        return self::get($kin, $fail);
    }

}