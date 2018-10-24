<?php

class State extends Genome {

    protected $lot = [];

    public function __construct($in = [], array $lot = []) {
        $this->lot = extend(Is::file($in) ? require $in : $in, $lot);
        parent::__construct();
    }

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $x = $this->__get($kin);
        $fail = array_shift($lot);
        $alt = array_shift($lot);
        if (is_callable($fail)) {
            return fn($fail, $this, [$x !== null ? $x : $alt]);
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