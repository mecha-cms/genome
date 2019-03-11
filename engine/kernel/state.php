<?php

class State extends Genome {

    protected $lot;

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $out = $this->lot[$kin] ?? null;
        if ($lot && is_callable($out)) {
            return fn($out, $lot, $this, static::class);
        }
        return $out;
    }

    public function __construct($in = null, array $lot = []) {
        extract(Lot::get(), EXTR_SKIP);
        $this->lot = array_replace_recursive((array) (Is::file($in) ? require $in : $in), $lot);
        parent::__construct();
    }

    public function __get(string $key) {
        if (method_exists($this, $key)) {
            if ((new \ReflectionMethod($this, $key))->isPublic()) {
                return $this->{$key}();
            }
        }
        return $this->__call($key);
    }

    // Fix case for `isset($state->key)` or `!empty($state->key)`
    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __set(string $key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __toString() {
        return json_encode($this->lot);
    }

    public function __unset(string $key) {
        unset($this->lot[$key]);
    }

}