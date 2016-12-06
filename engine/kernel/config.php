<?php

class Config extends Genome {

    protected static $bucket_static = [];

    protected static function ignite_static(...$lot) {
        $a = State::config();
        if (!$f = File::exist(LANGUAGE . DS . $a['language'] . '.txt')) {
            $f = LANGUAGE . DS . 'en-us.txt';
        }
        $a['__i18n'] = From::yaml(File::open($f)->read(""));
        self::$bucket_static = $a;
    }

    protected static function set_static($a, $b = null) {
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
        Anemon::extend(self::$bucket_static, $cargo);
    }

    protected static function get_static($a = null, $fail = false) {
        if ($a === null) {
            return !empty(self::$bucket_static) ? o(self::$bucket_static) : $fail;
        }
        if (__is_anemon__($a)) {
            $output = [];
            foreach ($a as $k => $v) {
                $output[$k] = self::get_static($k, $v);
            }
            return o($output);
        }
        return o(Anemon::get(self::$bucket_static, $a, $fail));
    }

    protected static function reset_static($a = null) {
        if ($a !== null) {
            foreach ((array) $a as $v) {
                Anemon::reset(self::$bucket_static, $v);
            }
        } else {
            self::$bucket_static = [];
        }
        return new static;
    }

    protected static function merge_static(...$lot) {
        call_user_func_array('self::set_static', $lot);
    }

    public static function __callStatic($kin, $lot) {
        if (!self::kin($kin)) {
            $fail = false;
            if (count($lot)) {
                $kin .= '.' . array_shift($lot);
                $fail = array_shift($lot) ?? false;
            }
            return self::get_static($kin, $fail);
        }
        return parent::__callStatic($kin, $lot);
    }

    public function __call($key, $lot) {
        $fail = false;
        if ($count = count($lot)) {
            if ($count > 1) {
                $key = $key . '.' . array_shift($lot);
            }
            $fail = array_shift($lot) ?? false;
        }
        if (is_string($fail) && strpos($fail, 'fn:') === 0) {
            return call_user_func(substr($fail, 3), self::get_static($key, false));
        } elseif ($fail instanceof \Closure) {
            return call_user_func($fail, self::get_static($key, false));
        }
        return self::get_static($key, $fail);
    }

    public function __get($key) {
        return self::get_static($key);
    }

    public function __set($key, $value = null) {
        self::set_static($key, $value);
    }

    public function __toString() {
        return json_encode(self::get_static());
    }

    public function __invoke($fail = []) {
        return self::get_static(null, o($fail));
    }

}