<?php

class State extends Genome {

    protected $lot;

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $out = $this->lot[p2f($kin)] ?? null;
        if ($lot) {
            if ($out instanceof \Closure) {
                return fire($out, $lot, $this, static::class);
            }
            if (is_callable($out) && !is_string($out)) {
                return call_user_func($out, ...$lot);
            }
        }
        return $out;
    }

    public function __construct(string $in = null, array $lot = []) {
        extract($GLOBALS, EXTR_SKIP);
        $this->lot = array_replace_recursive(is_file($in) ? require $in : [], $lot);
        parent::__construct();
    }

    public function __get(string $key) {
        return $this->__call($key);
    }

    // Fix case for `isset($state->key)` or `!empty($state->key)`
    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __toString() {
        return json_encode($this->lot);
    }

    public function __unset(string $key) {
        unset($this->lot[p2f($key)]);
    }

}