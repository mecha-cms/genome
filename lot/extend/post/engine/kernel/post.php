<?php

class Post {

    protected $a = [];

    public function __construct($a) {
        $this->a = $a;
        return $this;
    }

    public function __call($key, $lot) {
        $fail = array_shift($lot);
        if (is_callable($fail)) {
            return call_user_func($fail, $this->a[$key] ?? false);
        }
        return $this->a[$key] ?? $fail ?? false;
    }

    public function __set($key, $value = null) {
        $this->a[$key] = $value;
    }

    public function __get($key) {
        return $this->a[$key] ?? false;
    }

    public function __toString() {
        return json_encode($this->a);
    }

}