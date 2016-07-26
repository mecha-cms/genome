<?php

abstract class DNA {

    // Method(s) ...
    public $_ = [];

    // Boot a class once!
    protected static $boot = [];

    // Show the added method(s)
    public function kin($kin = null, $fail = false, $origin = false) {
        $c = static::class;
        if ($kin !== null) {
            if (!isset($this->_[0][$c][$kin])) {
                $output = $this->_[1][$c][$kin] ?? $fail;
                return $origin && method_exists($this, $kin) ? 1 : $output;
            }
            return $fail;
        }
        return !empty($this->_[1][$c]) ? $this->_[1][$c] : $fail;
    }

    // Add new method with `__::plug('foo')`
    public function plug($kin, $fn) {
        $this->_[1][static::class][$kin] = $fn;
    }

    // Remove the added method with `__::unplug('foo')`
    public function unplug($kin) {
        if ($kin === true) {
            $this->_ = [];
        } else {
            $c = static::class;
            $this->_[0][$c][$kin] = 1;
            unset($this->_[1][$c][$kin]);
        }
    }

    // Call the added method with `__::foo()`
    public function __callStatic($kin, $lot = []) {
        $c = static::class;
        if (!isset($this->boot[$c])) {
            $this->boot[$c] = new static;
        }
        return $this->boot[$c]->__call($kin, $lot);
    }

    // @ditto
    public function __call($kin, $lot = []) {
        $c = static::class;
        if (!isset($this->_[1][$c][$kin])) {
            exit('Method <code>' . $c . '::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array($this->_[1][$c][$kin], $lot);
    }

}