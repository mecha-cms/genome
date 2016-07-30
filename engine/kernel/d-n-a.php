<?php

abstract class DNA {

    // Method(s) ...
    public $_ = [];

    // Boot a class once!
    public $boot = [];

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

    // Add new method with `$instance->plug('foo')`
    public function plug($kin, $fn) {
        $this->_[1][static::class][$kin] = $fn;
    }

    // Remove the added method with `$instance->unplug('foo')`
    public function unplug($kin) {
        if ($kin === true) {
            $this->_ = [];
        } else {
            $c = static::class;
            $this->_[0][$c][$kin] = 1;
            unset($this->_[1][$c][$kin]);
        }
    }

    // Call the added method with `$instance->foo()`
    public function __call($kin, $lot = []) {
        $c = static::class;
		if (!isset($this->_[1][$c][$kin])) {
			exit('Method <code>' . $c . '::' . $kin . '()</code> does not exist.');
		}
        return call_user_func_array($this->_[1][$c][$kin], $lot);
    }

	public static function __callStatic($kin, $lot = []) {
		$c = static::class;
		if (!isset(static::$boot[$c])) {
			static::$boot[$c] = new static;
		}
		return call_user_func_array([static::$boot[$c], $kin], $lot);
	}

}