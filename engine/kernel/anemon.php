<?php

class Anemon extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \Serializable {

    public $lot = [];
    public $current = [];
    public $parent = null;
    public $separator = "";
    public $i = 0;

    public function offsetSet($i, $value) {
        if (!isset($i)) {
            $this->current[] = $value;
        } else {
            $this->current[$i] = $value;
        }
    }

    public function offsetExists($i) {
        return isset($this->current[$i]);
    }

    public function offsetUnset($i) {
        unset($this->current[$i]);
    }

    public function offsetGet($i) {
        return $this->current[$i] ?? null;
    }

    public function count($deep = false) {
        return count($this->current, $deep ? COUNT_RECURSIVE : COUNT_NORMAL);
    }

    public function getIterator() {
        return new \ArrayIterator($this->current);
    }

    public function serialize() {
        return serialize($this->current);
    }

    public function unserialize($lot) {
        $this->lot = $this->current = unserialize($lot);
    }

    public function __construct(iterable $array = [], string $separator = ', ') {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        $this->lot = $this->current = $array;
        $this->separator = $separator;
        parent::__construct();
    }

    public function __get($key) {
        if (method_exists($this, $key)) {
            return $this->{$key}();
        }
        return parent::__get($key);
    }

    public function __toString() {
        return $this->__invoke($this->separator);
    }

    public function __invoke(string $separator = ', ', $is = true) {
        return implode($separator, $is ? $this->is(function($v, $k) {
            // Ignore `null` and `false` value and all item(s) with key prefixed by a `_`
            return isset($v) && $v !== false && strpos($k, '_') !== 0;
        })->current : $this->current);
    }

    public function lot() {
        $this->current = $this->lot;
        return $this;
    }

    public function mitose() {
        $clone = new static($this->current, $this->separator);
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
        return reset($this->current);
    }

    // Get current array value
    public function current($fail = null) {
        $current = array_values($this->current);
        return $current[$this->i] ?? $fail;
    }

    // Get last array value
    public function last() {
        return end($this->current);
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
        $this->current = array_slice($this->current, 0, $i, true) + [$key ?? $i => $value] + array_slice($this->current, $i, null, true);
        return $this;
    }

    // Insert `$value` after current element
    public function after($value, $key = null) {
        $i = b($this->i + 1, 0, $this->count());
        $this->current = array_slice($this->current, 0, $i, true) + [$key ?? $i => $value] + array_slice($this->current, $i, null, true);
        return $this;
    }

    // Replace current element with `$value`
    public function replace($value) {
        $i = 0;
        foreach ($this->current as $k => $v) {
            if ($i === $this->i) {
                $this->current[$k] = $value;
                break;
            }
            ++$i;
        }
        return $this;
    }

    // Get array key by position
    public function key(int $index, $fail = null) {
        $array = array_keys($this->current);
        return array_key_exists($index, $array) ? $array[$index] : $fail;
    }

    // Get position by array key
    public function index(string $key, $fail = null) {
        $search = array_search($key, array_keys($this->current));
        return $search !== false ? $search : $fail;
    }

    // Generate chunk(s) of array
    public function chunk(int $chunk = 5, int $index = -1, $preserve_key = false) {
        $this->current = array_chunk($this->current, $chunk, $preserve_key);
        if ($index !== -1) {
            $this->current = $this->current[$this->i = $index] ?? [];
        }
        return $this;
    }

    // Sort array value: `1` for “asc” and `-1` for “desc”
    public function sort($sort = 1, $preserve_key = false) {
        if (is_array($sort) && isset($sort[1])) {
            $before = $after = [];
            $key = $sort[1];
            if (!empty($this->current)) {
                foreach ($this->current as $k => $v) {
                    $v = (array) $v;
                    if (array_key_exists($key, $v)) {
                        $before[$k] = $v[$key];
                    } else if (!is_bool($preserve_key)) {
                        $before[$k] = (string) $preserve_key;
                        $this->current[$k][$key] = (string) $preserve_key;
                    }
                }
                $sort[0] === -1 ? arsort($before) : asort($before);
                foreach ($before as $k => $v) {
                    $after[$k] = $this->current[$k];
                }
            }
            $this->current = $after;
            unset($before, $after);
        } else {
            if (is_array($sort)) {
                $sort = $sort[0];
            }
            $sort === -1 ? arsort($this->current) : asort($this->current);
        }
        if ($preserve_key === false) {
            $this->current = array_values($this->current);
        }
        return $this;
    }

    // @see `.\engine\ignite.php#fn:any`
    public function any($fn = null) {
        return any($this->current, $fn);
    }

    // @see `.\engine\ignite.php#fn:find`
    public function find(callable $fn = null, $fail = null) {
        $found = find($this->current, $fn);
        return $found !== null ? $found : $fail;
    }

    // @see `.\engine\ignite.php#fn:has`
    public function has(string $value = "", string $separator = X) {
        return has($this->current, $value, $separator);
    }

    // @see `.\engine\ignite.php#fn:is`
    public function is($fn = null) {
        $this->current = is($this->current, $fn);
        return $this;
    }

    // @see `.\engine\ignite.php#fn:map`
    public function map(callable $fn) {
        $this->current = map($this->current, $fn);
        return $this;
    }

    // @see `.\engine\ignite.php#fn:not`
    public function not($fn = null) {
        $this->current = $fn ? not($this->current, $fn) : [];
        return $this;
    }

    // @see `.\engine\ignite.php#fn:pluck`
    public function pluck(string $key, $fail = null) {
        $this->current = pluck($this->current, $key, $fail);
        return $this;
    }

    // @see `.\engine\ignite.php#fn:shake`
    public function shake($preserve_key = true) {
        $this->current = shake($this->current, $preserve_key);
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
            return self::get($this->current, $key, $fail);
        }
        return $this->current;
    }

}