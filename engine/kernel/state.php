<?php

class State extends Genome {

    protected $lot = [];

    public function __construct($input = [], $lot = []) {
        $this->lot = array_replace(is_string($input) && is_file($input) ? require $input : $input, $lot);
        parent::__construct();
    }

    public function __call($key, $lot = []) {
        if (self::_($key)) {
            return parent::__call($key, $lot);
        }
        $x = $this->__get($key);
        if (__is_instance__($x) && method_exists($x, '__invoke')) {
            return call_user_func_array($x, $lot);
        }
        $fail = array_shift($lot);
        $alt = array_shift($lot);
        if ($fail instanceof \Closure) {
            return call_user_func(\Closure::bind($fail, $this), $x !== null ? $x : $alt);
        }
        return $x !== null ? $x : $fail;
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return array_key_exists($key, $this->lot) ? $this->lot[$key] : null;
    }

    // Fix case for `isset($state->key)` or `!empty($state->key)`
    public function __isset($key) {
        return !!$this->__get($key);
    }

    public function __unset($key) {
        unset($this->lot[$key]);
    }

    public function __toString() {
        return json_encode($this->lot);
    }

}