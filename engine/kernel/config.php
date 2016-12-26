<?php

class Config extends Genome {

    protected static $bucket = [];

    public static function ignite(...$lot) {
        $a = State::config();
        if (!$f = File::exist(LANGUAGE . DS . $a['language'] . '.txt')) {
            $f = LANGUAGE . DS . 'en-us.txt';
        }
        $a['__i18n'] = From::yaml(File::open($f)->read(""));
        self::$bucket = $a;
    }

    public static function set($a, $b = null) {
        if (__is_anemon__($a)) $a = a($a);
        if (__is_anemon__($b)) $b = a($b);
        $cargo = [];
        if (!is_array($a)) {
            Anemon::set($cargo, $a, $b);
        } else {
            foreach ($a as $k => $v) {
                Anemon::set($cargo, $k, $v);
            }
        }
        Anemon::extend(self::$bucket, $cargo);
    }

    public static function get($a = null, $fail = false) {
        if (!isset($a)) {
            return !empty(self::$bucket) ? o(self::$bucket) : $fail;
        }
        if (__is_anemon__($a)) {
            $output = [];
            foreach ($a as $k => $v) {
                $output[$k] = self::get($k, $v);
            }
            return o($output);
        }
        return o(Anemon::get(self::$bucket, $a, $fail));
    }

    public static function reset($a = null) {
        if (isset($a)) {
            foreach ((array) $a as $v) {
                Anemon::reset(self::$bucket, $v);
            }
        } else {
            self::$bucket = [];
        }
        return new static;
    }

    public static function merge(...$lot) {
        call_user_func_array('self::set', $lot);
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
            $fail = array_shift($lot);
            $fail = $fail ? $fail : false;
        }
        if (is_string($fail) && strpos($fail, 'fn:') === 0) {
            return call_user_func(substr($fail, 3), self::get($key, false));
        } elseif ($fail instanceof \Closure) {
            return call_user_func($fail, self::get($key, false));
        }
        return self::get($key, $fail);
    }

    public function __get($key) {
        return self::get($key);
    }

    public function __set($key, $value = null) {
        self::set($key, $value);
    }

    public function __toString() {
        return json_encode(self::get());
    }

    public function __invoke($fail = []) {
        return self::get(null, o($fail));
    }

}