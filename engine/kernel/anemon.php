<?php

class Anemon extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \Serializable {

    public $lot = [];
    public $value = [];
    public $parent = null;
    public $separator = "";
    public $i = 0;

    public function offsetSet($i, $value) {
        if (!isset($i)) {
            $this->value[] = $value;
        } else {
            $this->value[$i] = $value;
        }
    }

    public function offsetExists($i) {
        return isset($this->value[$i]);
    }

    public function offsetUnset($i) {
        unset($this->value[$i]);
    }

    public function offsetGet($i) {
        return $this->value[$i] ?? null;
    }

    public function count($deep = false) {
        return count($this->value, $deep ? COUNT_RECURSIVE : COUNT_NORMAL);
    }

    public function getIterator() {
        return new \ArrayIterator($this->value);
    }

    public function serialize() {
        return serialize($this->value);
    }

    public function unserialize($lot) {
        $this->lot = $this->value = unserialize($lot);
    }

    public function __construct(iterable $array = [], string $separator = ', ') {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        $this->lot = $this->value = $array;
        $this->separator = $separator;
        parent::__construct();
    }

    public function __toString() {
        return $this->__invoke($this->separator);
    }

    public function __invoke(string $separator = ', ', $is = true) {
        return implode($separator, $is ? $this->is(function($v, $k) {
            // Ignore `null` and `false` value and all item(s) with key prefixed by a `_`
            return isset($v) && $v !== false && strpos($k, '_') !== 0;
        })->value : $this->value);
    }

    public function lot(array $lot = [], $over = false) {
        if ($lot) {
            $this->lot = $over ? extend($this->lot, $lot) : $lot;
        }
        $this->value = $this->lot;
        return $this;
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

    // Move to previous array index
    public function previous(int $skip = 0) {
        $this->i = b($this->i - 1 - $skip, 0, $this->count() - 1);
        return $this;
    }

    // Move to `$index` array index
    public function to($index) {
        $this->i = is_int($index) ? $index : $this->index($index, $index);
        return $this;
    }

    // Move to the first array
    public function begin() {
        $this->i = 0;
        return $this;
    }

    // Move to the last array
    public function end() {
        $this->i = $this->count() - 1;
        return $this;
    }

    // Get first array value
    public function first() {
        return reset($this->value);
    }

    // Get current array value
    public function current($fail = null) {
        $current = array_values($this->value);
        return $current[$this->i] ?? $fail;
    }

    // Get last array value
    public function last() {
        return end($this->value);
    }

    // Insert `$value` to the end of array
    public function append($value, $key = null) {
        return $this->end()->after($value, $key);
    }

    // Insert `$value` to the start of array
    public function prepend($value, $key = null) {
        return $this->begin()->before($value, $key);
    }

    // Insert `$value` before current element
    public function before($value, $key = null) {
        $i = b($this->i, 0, $this->count());
        $this->value = array_slice($this->value, 0, $i, true) + [$key ?? $i => $value] + array_slice($this->value, $i, null, true);
        return $this;
    }

    // Insert `$value` after current element
    public function after($value, $key = null) {
        $i = b($this->i + 1, 0, $this->count());
        $this->value = array_slice($this->value, 0, $i, true) + [$key ?? $i => $value] + array_slice($this->value, $i, null, true);
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

    // Get array key by position
    public function key(int $index, $fail = null) {
        $array = array_keys($this->value);
        return array_key_exists($index, $array) ? $array[$index] : $fail;
    }

    // Get position by array key
    public function index(string $key, $fail = null) {
        $search = array_search($key, array_keys($this->value));
        return $search !== false ? $search : $fail;
    }

    // Generate chunk(s) of array
    public function chunk(int $chunk = 5, int $index = -1, $preserve_key = false) {
        $this->value = array_chunk($this->value, $chunk, $preserve_key);
        if ($index !== -1) {
            $this->value = $this->value[$this->i = $index] ?? [];
        }
        return $this;
    }

    // Sort array value: `1` for “asc” and `-1` for “desc”
    public function sort($sort = 1, $preserve_key = false) {
        $value = $this->value;
        if (is_callable($sort)) {
            $preserve_key ? uasort($value, $sort) : usort($value, $sort);
        } else if (is_array($sort)) {
            $i = $sort[0];
            if (!isset($sort[1])) {
                if ($preserve_key) {
                    $i === -1 ? arsort($value) : asort($value);
                } else {
                    $i === -1 ? rsort($value) : sort($value);
                }
            } else {
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

    // @see `.\engine\ignite.php#fn:any`
    public function any($fn = null) {
        return any($this->value, $fn);
    }

    // @see `.\engine\ignite.php#fn:find`
    public function find(callable $fn = null, $fail = null) {
        $found = find($this->value, $fn);
        return $found !== null ? $found : $fail;
    }

    // @see `.\engine\ignite.php#fn:has`
    public function has(string $value = "", string $separator = X) {
        return has($this->value, $value, $separator);
    }

    // @see `.\engine\ignite.php#fn:is`
    public function is($fn = null) {
        $this->value = is($this->value, $fn);
        return $this;
    }

    // @see `.\engine\ignite.php#fn:map`
    public function map(callable $fn) {
        $this->value = map($this->value, $fn);
        return $this;
    }

    // @see `.\engine\ignite.php#fn:not`
    public function not($fn = null) {
        $this->value = $fn ? not($this->value, $fn) : [];
        return $this;
    }

    // @see `.\engine\ignite.php#fn:pluck`
    public function pluck(string $key, $fail = null) {
        $this->value = pluck($this->value, $key, $fail);
        return $this;
    }

    // @see `.\engine\ignite.php#fn:shake`
    public function shake($preserve_key = true) {
        $this->value = shake($this->value, $preserve_key);
        return $this;
    }

    // Create list of namespace step(s)
    public static function step($in, string $NS = '.', int $dir = 1) {
        if (is_string($in) && strpos($in, $NS) !== false) {
            $in = explode($NS, trim($in, $NS));
            $a = $dir === -1 ? array_pop($in) : array_shift($in);
            $out = [$a];
            if ($dir === -1) {
                while ($b = array_pop($in)) {
                    $a = $b . $NS . $a;
                    array_unshift($out, $a);
                }
            } else {
                while ($b = array_shift($in)) {
                    $a .= $NS . $b;
                    array_unshift($out, $a);
                }
            }
            return $out;
        }
        return (array) $in;
    }

    // Set array value recursively
    public static function set(array &$array, string $key, $value = null, string $NS = '.') {
        $keys = explode($NS, str_replace('\\' . $NS, X, $key));
        while (count($keys) > 1) {
            $key = str_replace(X, $NS, array_shift($keys));
            if (!array_key_exists($key, $array)) {
                $array[$key] = [];
            }
            $array =& $array[$key];
        }
        return ($array[array_shift($keys)] = $value);
    }

    // Get array value recursively
    public static function get(array &$array, string $key, $fail = false, string $NS = '.') {
        $keys = explode($NS, str_replace('\\' . $NS, X, $key));
        foreach ($keys as $value) {
            $value = str_replace(X, $NS, $value);
            if (!is_array($array) || !array_key_exists($value, $array)) {
                return $fail;
            }
            $array =& $array[$value];
        }
        return $array;
    }

    // Remove array value recursively
    public static function reset(array &$array, string $key, string $NS = '.') {
        $keys = explode($NS, str_replace('\\' . $NS, X, $key));
        while (count($keys) > 1) {
            $key = str_replace(X, $NS, array_shift($keys));
            if (array_key_exists($key, $array)) {
                $array =& $array[$key];
            }
        }
        if (is_array($array) && array_key_exists($value = array_shift($keys), $array)) {
            unset($array[$value]);
        }
        return $array;
    }

    public static function eat(iterable $array) {
        return new static($array);
    }

    public function vomit($key = null, $fail = null) {
        if (isset($key)) {
            return self::get($this->value, $key, $fail);
        }
        return $this->value;
    }

}