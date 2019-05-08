<?php

class Config extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable, \Serializable {

    protected static $a = [];
    protected static $lot = [];

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        return self::__callStatic($kin, $lot);
    }

    public function __get(string $key) {
        if (self::_($key)) {
            return $this->__call($key);
        }
        return self::get(p2f($key));
    }

    public function __invoke(...$v) {
        return count($v) === 1 ? self::get(...$v) : self::set(...$v);
    }

    // Fix case for `isset($config->key)` or `!empty($config->key)`
    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __set(string $key, $value = null) {
        return self::set(p2f($key), $value);
    }

    public function __toString() {
        return json_encode(self::get());
    }

    public function __unset(string $key) {
        self::let(p2f($key));
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $kin = p2f($kin); // `fooBar_baz` → `foo-bar_baz`
        if ($lot) {
            $out = self::get($kin);
            // Asynchronous value with function closure
            if ($out instanceof \Closure) {
                return fire($out, $lot, null, static::class);
            }
            // Rich asynchronous value with class instance
            if (is_callable($out) && !is_string($out)) {
                return call_user_func($out, ...$lot);
            }
            // Else, static value
            return self::get($kin . '.' . array_shift($lot), !!array_shift($lot));
        }
        return self::get($kin);
    }

    public static function alt(...$lot) {
        $c = static::class;
        self::set(...$lot);
        self::$lot[$c] = array_replace_recursive(self::$lot[$c], self::$a[$c]);
    }

    public function count($deep = false) {
        return count(self::$lot[static::class] ?? [], $deep ? COUNT_RECURSIVE : COUNT_NORMAL);
    }

    public static function get($key = null, $array = false) {
        $c = static::class;
        if (is_array($key)) {
            $out = [];
            foreach ($key as $k => $v) {
                $out[$k] = self::get($k, $array) ?? $v;
            }
            return $array ? $out : o($out);
        } else if (isset($key)) {
            $out = self::$lot[$c] ?? [];
            $out = get($out, $key);
            return $array ? $out : o($out);
        }
        $out = self::$lot[$c] ?? [];
        return $array ? $out : o($out);
    }

    public function getIterator() {
        return new \ArrayIterator(self::$lot[static::class] ?? []);
    }

    public function jsonSerialize() {
        return self::$lot[static::class] ?? [];
    }

    public static function load(...$lot) {
        $c = static::class;
        if (isset($lot[0])) {
            $a = Is::file($lot[0]) ? require $lot[0] : $lot[0];
            return (self::$lot[$c] = self::$a[$c] = a($a));
        }
        return (self::$lot[$c] = []);
    }

    public static function let($key = null) {
        $c = static::class;
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key)) {
            let(self::$lot[$c], $key);
        } else {
            self::$lot[$c] = [];
        }
    }

    public function offsetExists($i) {
        return isset(self::$lot[static::class][$i]);
    }

    public function offsetGet($i) {
        return self::$lot[static::class][$i] ?? null;
    }

    public function offsetSet($i, $value) {
        $c = static::class;
        if (isset($i)) {
            self::$lot[$c][$i] = $value;
        } else {
            self::$lot[$c][] = $value;
        }
    }

    public function offsetUnset($i) {
        unset(self::$lot[static::class][$i]);
    }

    public function serialize() {
        return serialize(self::$lot[static::class] ?? []);
    }

    public static function set($key, $value = null) {
        $c = static::class;
        $in = [];
        if (is_array($key)) {
            $in = $key;
        } else {
            set($in, $key, $value);
        }
        $out = self::$lot[$c] ?? [];
        self::$lot[$c] = array_replace_recursive($out, $in);
    }

    public function unserialize($v) {
        self::$lot[static::class] = unserialize($v);
    }

}