<?php

abstract class __ {

    public $_ = [];
    public $_x = [];

    // Show the added method(s)
    public function kin($kin = null, $fail = false, $origin = false) {
        $c = static::class;
        if ($kin !== null) {
            if (!isset($this->_x[$c][$kin])) {
                $output = $this->_[$c][$kin] ?? $fail;
                return $origin && method_exists($this, $kin) ? 1 : $output;
            }
            return $fail;
        }
        return !empty($this->_[$c]) ? $this->_[$c] : $fail;
    }

    // Add new method with `__::plug('foo')`
    public function plug($kin, $fn) {
        $this->_[static::class][$kin] = $fn;
    }

    // Remove the added method with `__::unplug('foo')`
    public function unplug($kin) {
        if ($kin === true) {
            $this->_ = $this->_x = [];
        } else {
            $c = static::class;
            $this->_x[$c][$kin] = 1;
            unset($this->_[$c][$kin]);
        }
    }

    // Call the added method with `__::foo()`
    public static function __callStatic($kin, $lot = []) {
        $self = new static;
        return $self->__call($kin, $lot);
    }

    // @ditto
    public function __call($kin, $lot = []) {
        $c = static::class;
        if (!isset($this->_[$c][$kin])) {
            exit('Method <code>' . $c . '::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array($this->_[$c][$kin], $lot);
    }

}