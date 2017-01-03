<?php

class Config extends Genome {

    protected static $bucket = [];

    public static function ignite(...$lot) {
        $a = State::config();
        if (!$f = File::exist(LANGUAGE . DS . $a['language'] . '.txt')) {
            $f = LANGUAGE . DS . 'en-us.txt';
        }
        $a['__i18n'] = From::yaml($f);
        return (self::$bucket = $a);
    }

    public static function set($key, $value = null) {
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
        return Anemon::extend(self::$bucket, $cargo);
    }

    public static function get($key = null, $fail = false) {
        if (!isset($key)) {
            return !empty(self::$bucket) ? o(self::$bucket) : $fail;
        }
        if (__is_anemon__($key)) {
            $output = [];
            foreach ($key as $k => $v) {
                $output[$k] = self::get($k, $v);
            }
            return o($output);
        }
        return o(Anemon::get(self::$bucket, $key, $fail));
    }

    public static function reset($key = null) {
        if (isset($key)) {
            foreach ((array) $key as $value) {
                Anemon::reset(self::$bucket, $value);
            }
        } else {
            self::$bucket = [];
        }
        return new static;
    }

    public static function merge(...$lot) {
        return call_user_func_array('self::set', $lot);
    }

    public static function __callStatic($kin, $lot) {
        if (!self::kin($kin)) {
            $fail = false;
            if (count($lot)) {
                $kin .= '.' . array_shift($lot);
                $fail = array_shift($lot);
                $fail = $fail ? $fail : false;
            }
            return self::get($kin, $fail);
        }
        return parent::__callStatic($kin, $lot);
    }

    public function __call($key, $lot) {
        $fail = false;
        if ($count = count($lot)) {
            if ($count > 1) {
                $key = $key . '.' . array_shift($lot);
            }
            $fail = array_shift($lot) ?: false;
            $fail_alt = array_shift($lot) ?: false;
        }
        if (is_string($fail) && strpos($fail, '~') === 0) {
            return call_user_func(substr($fail, 1), self::get($key, $fail_alt));
        } else if ($fail instanceof \Closure) {
            return call_user_func($fail, self::get($key, $fail_alt));
        }
        return self::get($key, $fail);
    }

    public function __get($key) {
        return self::get($key);
    }

    public function __set($key, $value = null) {
        return self::set($key, $value);
    }

    public function __toString() {
        return To::yaml(self::get());
    }

    public function __invoke($fail = []) {
        return self::get(null, o($fail));
    }

}