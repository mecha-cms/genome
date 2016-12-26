<?php

class Post {

    protected $lot = [];

    public function __construct($a, $lot = [], $NS = 'post.') {
        if (!__is_anemon__($a)) {
            $a = Page::open($a)->read($lot, $NS);
        }
        $this->lot = $a;
    }

    public function __call($key, $lot) {
        $fail = array_shift($lot);
        if (is_string($fail) && strpos($fail, 'fn:') === 0) {
            return call_user_func(substr($fail, 3), array_key_exists($key, $this->lot) ? o($this->lot[$key]) : false);
        } else if ($fail instanceof \Closure) {
            return call_user_func($fail, array_key_exists($key, $this->lot) ? o($this->lot[$key]) : false);
        }
        return array_key_exists($key, $this->lot) ? o($this->lot[$key]) : (isset($fail) ? $fail : false);
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return array_key_exists($key, $this->lot) ? o($this->lot[$key]) : false;
    }

    public function __toString() {
        return json_encode($this->lot);
    }

}