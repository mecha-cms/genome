<?php

final class Anemon extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \Serializable {

    public $i = 0;
    public $lot = [];
    public $parent = null;
    public $separator = "";
    public $value = [];

    public function __construct(iterable $array = [], string $separator = ', ') {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        $this->lot = $this->value = $array;
        $this->separator = $separator;
        parent::__construct();
    }

    public function __invoke(string $separator = ', ', $is = true) {
        return implode($separator, $is ? $this->is(function($v, $k) {
            // Ignore `null` and `false` value and all item(s) with key prefixed by a `_`
            return isset($v) && $v !== false && strpos($k, '_') !== 0;
        })->value : $this->value);
    }

    public function __toString() {
        return $this->__invoke($this->separator);
    }

    // Insert `$value` after current element
    public function after($value, $key = null) {
        $i = b($this->i + 1, 0, $this->count());
        $this->value = array_slice($this->value, 0, $i, true) + [$key ?? $i => $value] + array_slice($this->value, $i, null, true);
        return $this;
    }

    // @see `.\engine\ignite.php#fn:any`
    public function any($fn = null) {
        return any($this->value, $fn);
    }

    // Insert `$value` to the end of array
    public function append($value, $key = null) {
        $this->i = count($v = $this->value) + 1;
        if (isset($key)) {
            $v += [$key => $value];
        } else {
            $v[] = $value;
        }
        $this->value = $v;
        return $this;
    }

    // Insert `$value` before current element
    public function before($value, $key = null) {
        $i = b($this->i, 0, $this->count());
        $this->value = array_slice($this->value, 0, $i, true) + [$key ?? $i => $value] + array_slice($this->value, $i, null, true);
        return $this;
    }

    // Move to the first array
    public function begin() {
        $this->i = 0;
        return $this;
    }

    // Generate chunk(s) of array
    public function chunk(int $chunk = 5, int $index = -1, $preserve_key = false) {
        $clone = $this->mitose();
        $clone->value = array_chunk($clone->value, $chunk, $preserve_key);
        if ($index !== -1) {
            $clone->value = $clone->value[$clone->i = $index] ?? [];
        }
        return $clone;
    }

    public function count($deep = false) {
        return count($this->value, $deep ? COUNT_RECURSIVE : COUNT_NORMAL);
    }

    // Get current array value
    public function current() {
        $current = array_values($this->value);
        return $current[$this->i] ?? null;
    }

    // Move to the last array
    public function end() {
        $this->i = $this->count() - 1;
        return $this;
    }

    // @see `.\engine\ignite.php#fn:find`
    public function find(callable $fn = null) {
        return find($this->value, $fn);
    }

    // Get first array value
    public function first($take = false) {
        return $take ? array_shift($this->value) : reset($this->value);
    }

    public function getIterator() {
        return new \ArrayIterator($this->value);
    }

    // @see `.\engine\ignite.php#fn:has`
    public function has(string $value = "", string $separator = X) {
        return has($this->value, $value, $separator);
    }

    // Get position by array key
    public function index(string $key) {
        $i = array_search($key, array_keys($this->value));
        return $i !== false ? $search : null;
    }

    // @see `.\engine\ignite.php#fn:is`
    public function is($fn = null) {
        $clone = $this->mitose();
        $clone->value = is($clone->value, $fn);
        return $clone;
    }

    // Get array key by position
    public function key(int $index) {
        $array = array_keys($this->value);
        return array_key_exists($index, $array) ? $array[$index] : null;
    }

    // Get last array value
    public function last($take = false) {
        return $take ? array_pop($this->value) : end($this->value);
    }

    public function lot(array $lot = [], $over = false) {
        if ($lot) {
            $this->lot = $over ? extend($this->lot, $lot) : $lot;
        }
        $this->value = $this->lot;
        return $this;
    }

    // @see `.\engine\ignite.php#fn:map`
    public function map(callable $fn) {
        $clone = $this->mitose();
        $clone->value = map($clone->value, $fn);
        return $clone;
    }

    // Clone the current instance
    public function mitose() {
        $clone = new static($this->value, $this->separator);
        $clone->lot = $this->lot;
        $clone->parent = $this;
        return $clone;
    }

    // Move to next array index
    public function next(int $skip = 0) {
        $this->i = b($this->i + 1 + $skip, 0, $this->count() - 1);
        return $this;
    }

    // @see `.\engine\ignite.php#fn:not`
    public function not($fn = null) {
        $clone = $this->mitose();
        $clone->value = not($clone->value, $fn);
        return $clone;
    }

    public function offsetExists($i) {
        return isset($this->value[$i]);
    }

    public function offsetGet($i) {
        return $this->value[$i] ?? null;
    }

    public function offsetSet($i, $value) {
        if (isset($i)) {
            $this->value[$i] = $value;
        } else {
            $this->value[] = $value;
        }
    }

    public function offsetUnset($i) {
        unset($this->value[$i]);
    }

    // @see `.\engine\ignite.php#fn:pluck`
    public function pluck(string $key, $alt = null) {
        $clone = $this->mitose();
        $clone->value = pluck($clone->value, $key, $alt);
        return $clone;
    }

    // Insert `$value` to the start of array
    public function prepend($value, $key = null) {
        $this->i = 0;
        $v = $this->value;
        if (isset($key)) {
            $v = [$key => $value] + $v;
        } else {
            array_unshift($v, $value);
        }
        $this->value = $v;
        return $this;
    }

    // Move to previous array index
    public function previous(int $skip = 0) {
        $this->i = b($this->i - 1 - $skip, 0, $this->count() - 1);
        return $this;
    }

    // Replace current element with `$value`
    public function replace($value) {
        $i = 0;
        foreach ($this->value as $k => $v) {
            if ($i === $this->i) {
                $this->value[$k] = $value;
                break;
            }
            ++$i;
        }
        return $this;
    }

    public function serialize() {
        return serialize($this->value);
    }

    // @see `.\engine\ignite.php#fn:shake`
    public function shake($preserve_key = true) {
        $this->value = shake($this->value, $preserve_key);
        return $this;
    }

    // Sort array value: `1` for “asc” and `-1` for “desc”
    public function sort($sort = 1, $preserve_key = false) {
        $value = $this->value;
        if (is_callable($sort)) {
            $preserve_key ? uasort($value, $sort) : usort($value, $sort);
        } else if (is_array($sort)) {
            $i = $sort[0];
            if (isset($sort[1])) {
                $key = $sort[1];
                $fn = $i === -1 ? function($a, $b) use($key) {
                    if (!isset($a[$key]) && !isset($b[$key]))
                        return 0;
                    if (!isset($b[$key]))
                        return 1;
                    if (!isset($a[$key]))
                        return -1;
                    return $b[$key] <=> $a[$key];
                } : function($a, $b) use($key) {
                    if (!isset($a[$key]) && !isset($b[$key]))
                        return 0;
                    if (!isset($a[$key]))
                        return 1;
                    if (!isset($b[$key]))
                        return -1;
                    return $a[$key] <=> $b[$key];
                };
                if (array_key_exists(2, $sort)) {
                    foreach ($value as &$v) {
                        if (!isset($v[$key])) {
                            $v[$key] = $sort[2];
                        }
                    }
                    unset($v);
                }
                $preserve_key ? uasort($value, $fn) : usort($value, $fn);
            } else {
                if ($preserve_key) {
                    $i === -1 ? arsort($value) : asort($value);
                } else {
                    $i === -1 ? rsort($value) : sort($value);
                }
            }
        } else {
            if ($preserve_key) {
                $sort === -1 ? arsort($value) : asort($value);
            } else {
                $sort === -1 ? rsort($value) : sort($value);
            }
        }
        $this->value = $value;
        return $this;
    }

    // Move to `$index` array index
    public function to($index) {
        $this->i = is_int($index) ? $index : $this->index($index, $index);
        return $this;
    }

    public function unserialize($lot) {
        $this->lot = $this->value = unserialize($lot);
    }

    public function vomit($key = null) {
        if (isset($key)) {
            return self::get($this->value, $key);
        }
        return $this->value;
    }

    public static function eat(iterable $array) {
        return new static($array);
    }

    // Get array value recursively
    public static function get(array &$array, string $key, string $separator = '.') {
        $keys = explode($separator, str_replace("\\" . $separator, X, $key));
        foreach ($keys as $value) {
            $value = str_replace(X, $separator, $value);
            if (!is_array($array) || !array_key_exists($value, $array)) {
                return null;
            }
            $array =& $array[$value];
        }
        return $array;
    }

    // Remove array value recursively
    public static function reset(array &$array, string $key, string $separator = '.') {
        $keys = explode($separator, str_replace("\\" . $separator, X, $key));
        while (count($keys) > 1) {
            $key = str_replace(X, $separator, array_shift($keys));
            if (array_key_exists($key, $array)) {
                $array =& $array[$key];
            }
        }
        if (is_array($array) && array_key_exists($value = array_shift($keys), $array)) {
            unset($array[$value]);
        }
        return $array;
    }

    // Set array value recursively
    public static function set(array &$array, string $key, $value = null, string $separator = '.') {
        $keys = explode($separator, str_replace("\\" . $separator, X, $key));
        while (count($keys) > 1) {
            $key = str_replace(X, $separator, array_shift($keys));
            if (!array_key_exists($key, $array)) {
                $array[$key] = [];
            }
            $array =& $array[$key];
        }
        return ($array[array_shift($keys)] = $value);
    }

    // Create list of namespace step(s)
    public static function step($in, string $separator = '.', int $dir = 1) {
        if (is_string($in) && strpos($in, $separator) !== false) {
            $in = explode($separator, trim($in, $separator));
            $a = $dir === -1 ? array_pop($in) : array_shift($in);
            $out = [$a];
            if ($dir === -1) {
                while ($b = array_pop($in)) {
                    $a = $b . $separator . $a;
                    array_unshift($out, $a);
                }
            } else {
                while ($b = array_shift($in)) {
                    $a .= $separator . $b;
                    array_unshift($out, $a);
                }
            }
            return $out;
        }
        return (array) $in;
    }

}