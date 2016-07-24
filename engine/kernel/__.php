<?php

class __ {

    public $_ = [];
    public $_x = [];

    // Show the added method(s)
    public function kin($kin = null, $fail = false, $origin = false) {
        $c = get_called_class();
        if ($kin !== null) {
            if (!isset($this->_x[$c][$kin])) {
                $output = $this->_[$c][$kin] ?? $fail;
                return $origin && is_callable($c . '::' . $kin) ? 1 : $output;
            }
            return $fail;
        }
        if ($kin === true) {
            return !empty($this->_) ? $this->_ : $fail;
        }
        return !empty($this->_[$c]) ? $this->_[$c] : $fail;
    }

    // Add new method with `__::plug('foo')`
    public function plug($kin, $fn) {
        $this->_[get_called_class()][$kin] = $fn;
    }

    // Remove the added method with `__::unplug('foo')`
    public function unplug($kin) {
        if ($kin === true) {
            $this->_ = $this->_x = [];
        } else {
            $c = get_called_class();
            $this->_x[$c][$kin] = 1;
            unset($this->_[$c][$kin]);
        }
    }

    // Call the added method with `__::foo()`
    public static function __callStatic($kin, $lot = []) {
        $self = new self;
        return $self->__call($kin, $lot);
    }

    // @ditto
    public function __call($kin, $lot = []) {
        $c = get_called_class();
        if (!isset($this->_[$c][$kin])) {
            exit('Method <code>' . $c . '::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array($this->_[$c][$kin], $lot);
    }

}