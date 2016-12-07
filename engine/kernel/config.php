<?php

class Config extends Genome {

    protected static $bucket_ = [];

    protected static function ignite_(...$lot) {
        $a = State::config();
        if (!$f = File::exist(LANGUAGE . DS . $a['language'] . '.txt')) {
            $f = LANGUAGE . DS . 'en-us.txt';
        }
        $a['__i18n'] = From::yaml(File::open($f)->read(""));
        self::$bucket_ = $a;
    }

    protected static function set_($a, $b = null) {
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
        Anemon::extend(self::$bucket_, $cargo);
    }

    protected static function get_($a = null, $fail = false) {
        if ($a === null) {
            return !empty(self::$bucket_) ? o(self::$bucket_) : $fail;
        }
        if (__is_anemon__($a)) {
            $output = [];
            foreach ($a as $k => $v) {
                $output[$k] = self::get_($k, $v);
            }
            return o($output);
        }
        return o(Anemon::get(self::$bucket_, $a, $fail));
    }

    protected static function reset_($a = null) {
        if ($a !== null) {
            foreach ((array) $a as $v) {
                Anemon::reset(self::$bucket_, $v);
            }
        } else {
            self::$bucket_ = [];
        }
        return new static;
    }

    protected static function merge_(...$lot) {
        call_user_func_array('self::set_', $lot);
    }

    public static function __callStatic($kin, $lot) {
        if (!self::kin($kin)) {
            $fail = false;
            if (count($lot)) {
                $kin .= '.' . array_shift($lot);
                $fail = array_shift($lot) ?? false;
            }
            return self::get_($kin, $fail);
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
            return call_user_func(substr($fail, 3), self::get_($key, false));
        } elseif ($fail instanceof \Closure) {
            return call_user_func($fail, self::get_($key, false));
        }
        return self::get_($key, $fail);
    }

    public function __get($key) {
        return self::get_($key);
    }

    public function __set($key, $value = null) {
        self::set_($key, $value);
    }

    public function __toString() {
        return json_encode(self::get_());
    }

    public function __invoke($fail = []) {
        return self::get_(null, o($fail));
    }

}