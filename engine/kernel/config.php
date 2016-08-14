<?php

class Config extends Genome {

    protected static $bucket = [];

    public static function start(...$lot) {
        $a = State::config();
        if (!$f = File::exist(LANGUAGE . DS . $a['language'] . '.txt')) {
            $f = LANGUAGE . DS . 'en-us.txt';
        }
        $a['__i18n'] = From::yaml(File::open($f)->read(""));
        self::$bucket = $a;
    }

    public static function set($a, $b = null) {
        if (_is_anemon_($a)) $a = a($a);
        if (_is_anemon_($b)) $b = a($b);
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
        if ($a === null) {
            return !empty(self::$bucket) ? o(self::$bucket) : $fail;
        }
        if (_is_anemon_($a)) {
            $output = [];
            foreach ($a as $k => $v) {
                $f = is_array($fail) && array_key_exists($k, $fail) ? $fail[$k] : $fail;
                $output[$v] = self::get($v, $f);
            }
            return o($output);
        }
        if (is_string($a) && strpos($a, '.') !== false) {
            $output = Anemon::get(self::$bucket, $a, $fail);
            return is_array($output) ? o($output) : $output;
        }
        return array_key_exists($a, self::$bucket) ? o(self::$bucket[$a]) : $fail;
    }

    public static function reset($k = null) {
        if ($k !== null) {
            Anemon::reset(self::$bucket, $k);
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
                $fail = array_shift($lot) ?? false;
            }
            return self::get($kin, $fail);
        }
        return parent::__callStatic($kin, $lot);
    }

}