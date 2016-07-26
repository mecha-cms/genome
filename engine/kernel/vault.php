<?php

class Vault extends __ {

    protected static $bucket = [];

    public static function set($a, $b = null) {
        if (is_object($b) || is_array($b)) $b = a($b);
        $cargo = [];
        if (!is_array($a)) {
            Group::set($cargo, $a, $b);
        } else {
            foreach (a($a) as $k => $v) {
                Group::set($cargo, $k, $v);
            }
        }
        Group::extend(self::$bucket, $cargo);
    }

    public static function get($a = null, $fail = false) {
        if ($a === null) return o(self::$bucket);
        if (is_array($a)) {
            $output = [];
            foreach ($a as $k => $v) {
                $f = is_array($fail) && array_key_exists($k, $fail) ? $fail[$k] : $fail;
                $output[$v] = self::get($v, $f);
            }
            return (object) $output;
        }
        if (is_string($a) && strpos($a, '.') !== false) {
            $output = Group::get(self::$bucket, $a, $fail);
            return is_array($output) ? o($output) : $output;
        }
        return array_key_exists($a, self::$bucket) ? o(self::$bucket[$a]) : $fail;
    }

    public static function reset($k = null) {
        if ($k !== null) {
            Group::R(self::$bucket, $k);
        } else {
            self::$bucket = [];
        }
        return new static;
    }

    public static function merge() {
        call_user_func_array('self::set', func_get_args());
    }

    // Call the added method or use them as a shortcut for the default `get` method.
    // Example: You can use `Cargo::foo()` as a shortcut for `Cargo::get('foo')` as
    // long as `foo` is not defined yet by `Cargo::plug()`
    // NOTE: `Cargo::plug()` and `Cargo::kin()` method(s) are inherit of `__`
    public function __call($kin, $lot = []) {
        $c = static::class;
        if (!isset($this->_[$c][$kin])) {
            $fail = false;
            if (count($lot)) {
                $kin .= '.' . array_shift($lot);
                $fail = array_shift($lot);
            }
            return self::get($kin, $fail);
        }
        return parent::__call($kin, $lot);
    }

}