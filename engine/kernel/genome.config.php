<?php namespace Genome;

class Config extends \Genome {

    public function __call($key, $lot) {
        $fail = false;
        if ($count = count($lot)) {
            if ($count > 1) {
                $key = $key . '.' . array_shift($lot);
            }
            $fail = array_shift($lot) ?? false;
        }
        if (is_string($fail) && strpos($fail, '~') === 0) {
            return call_user_func(substr($fail, 1), \Config::get($key, false));
        } elseif ($fail instanceof \Closure) {
            return call_user_func($fail, \Config::get($key, false));
        }
        return \Config::get($key, $fail);
    }

    public function __get($key) {
        return \Config::get($key);
    }

    public function __set($key, $value = null) {
        \Config::set($key, $value);
    }

    public function __toString() {
        return json_encode(\Config::get());
    }

    public function __invoke($fail = []) {
        return \Config::get(null, o($fail));
    }

}