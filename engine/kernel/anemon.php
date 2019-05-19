<?php

final class Anemon extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable, \Serializable {

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
        return (string) $this->__invoke($this->separator);
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

    // Generate chunk(s) of array
    public function chunk(int $chunk = 5, int $i = -1, $preserve_key = false) {
        $clone = $this->mitose();
        $clone->value = array_chunk($clone->value, $chunk, $preserve_key);
        if ($i !== -1) {
            $clone->value = $clone->value[$clone->i = $i] ?? [];
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

    // @see `.\engine\ignite.php#fn:find`
    public function find(callable $fn = null) {
        return find($this->value, $fn);
    }

    // Get first array value
    public function first($take = false) {
        return $take ? array_shift($this->value) : reset($this->value);
    }

    public function get(string $key = null) {
        return isset($key) ? get($this->value, $key) : $this->value;
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

    public function jsonSerialize() {
        return $this->lot;
    }

    // Get array key by position
    public function key(int $i) {
        $array = array_keys($this->value);
        return array_key_exists($i, $array) ? $array[$i] : null;
    }

    // Get last array value
    public function last($take = false) {
        return $take ? array_pop($this->value) : end($this->value);
    }

    public function let(string $key) {
        let($this->value, $key);
        return $this;
    }

    public function lot(array $lot = [], $over = false) {
        if ($lot) {
            $this->lot = $over ? array_replace_recursive($this->lot, $lot) : $lot;
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
    public function prev(int $skip = 0) {
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

    public function set(string $key, $value) {
        set($this->value, $key, $value);
        return $this;
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
                    if (!isset($a[$key]) && !isset($b[$key])) {
                        return 0;
                    }
                    if (!isset($b[$key])) {
                        return 1;
                    }
                    if (!isset($a[$key])) {
                        return -1;
                    }
                    return $b[$key] <=> $a[$key];
                } : function($a, $b) use($key) {
                    if (!isset($a[$key]) && !isset($b[$key])) {
                        return 0;
                    }
                    if (!isset($a[$key])) {
                        return 1;
                    }
                    if (!isset($b[$key])) {
                        return -1;
                    }
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

    // Move to `$i` array
    public function to($i) {
        $this->i = is_int($i) ? $i : ($this->index($i) ?? $i);
        return $this;
    }

    // Move to the first array
    public function toFirst() {
        $this->i = 0;
        return $this;
    }

    // Move to the last array
    public function toLast() {
        $this->i = $this->count() - 1;
        return $this;
    }

    public function unserialize($v) {
        $this->lot = $this->value = unserialize($v);
    }

    public function vomit(string $key = null) {
        return $this->get($key);
    }

    public static function eat(iterable $array) {
        return new static($array);
    }

}