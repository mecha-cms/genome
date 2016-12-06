<?php

class Post {

    protected $lot = [];

    public function __construct($a, $content = 'content', $lot = [], $NS = 'post:') {
        if (!__is_anemon__($a)) {
            $a = Page::open($a)->read($content, $lot, $NS);
        }
        $this->lot = $a;
        $this->lot['date'] = new Date($a['time'] ?? time());
    }

    public function __call($key, $lot) {
        $fail = array_shift($lot);
        if (is_string($fail) && strpos($fail, 'fn:') === 0) {
            return call_user_func(substr($fail, 3), $this->lot[$key] ?? false);
        } elseif ($fail instanceof \Closure) {
            return call_user_func($fail, $this->lot[$key] ?? false);
        }
        return $this->lot[$key] ?? $fail ?? false;
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return $this->lot[$key] ?? false;
    }

    public function __toString() {
        return json_encode($this->lot);
    }

}