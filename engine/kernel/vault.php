<?php

class Vault extends DNA {

    protected $bucket = [];

    public function set($a, $b = null) {
        if (is_object($b) || is_array($b)) $b = a($b);
        $cargo = [];
        if (!is_array($a)) {
            Anemon::set($cargo, $a, $b);
        } else {
            foreach (a($a) as $k => $v) {
                Anemon::set($cargo, $k, $v);
            }
        }
        Anemon::extend($this->bucket, $cargo);
    }

    public function get($a = null, $fail = false) {
        if ($a === null) return o($this->bucket);
        if (is_array($a)) {
            $output = [];
            foreach ($a as $k => $v) {
                $f = is_array($fail) && array_key_exists($k, $fail) ? $fail[$k] : $fail;
                $output[$v] = $this->get($v, $f);
            }
            return (object) $output;
        }
        if (is_string($a) && strpos($a, '.') !== false) {
            $output = Anemon::get($this->bucket, $a, $fail);
            return is_array($output) ? o($output) : $output;
        }
        return array_key_exists($a, $this->bucket) ? o($this->bucket[$a]) : $fail;
    }

    public function reset($k = null) {
        if ($k !== null) {
            Anemon::reset($this->bucket, $k);
        } else {
            $this->bucket = [];
        }
        return $this;
    }

    public function merge() {
        call_user_func_array([$this, 'set'], func_get_args());
    }

    // Call the added method or use them as a shortcut for the default `get` method.
    // Example: You can use `Cargo::foo()` as a shortcut for `Cargo::get('foo')` as
    // long as `foo` is not defined yet by `Cargo::plug()`
    // NOTE: `Cargo::plug()` and `Cargo::kin()` method(s) are inherit of `__`
    public function __call($kin, $lot = []) {
        $c = static::class;
        if (!isset($this->_[1][$c][$kin])) {
            $fail = false;
            if (count($lot)) {
                $kin .= '.' . array_shift($lot);
                $fail = array_shift($lot);
            }
            return $this->get($kin, $fail);
        }
        return parent::__call($kin, $lot);
    }

}