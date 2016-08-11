<?php namespace Gene;

class Config extends \Socket {

    public function __call($key, $lot) {
        $fail = false;
        if ($count = count($lot)) {
            if ($count > 1) {
                $key = $key . '.' . array_shift($lot);
            }
            $fail = array_shift($lot) ?? false;
        }
        return \Config::get($key, $fail);
    }

    public function __set($key, $value = null) {
        \Config::set($key, $value);
    }

    public function __get($key) {
        return \Config::get($key);
    }

}