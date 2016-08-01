<?php

class Anemon extends __ {

    protected static $bucket = [];
    protected static $i = 0;

    // Prevent `$x` exceeds the value of `$min` and `$max`
    public static function edge($x, $min = 0, $max = 9999) {
        if ($x < $min) return $min;
        if ($x > $max) return $max;
        return $x;
    }

    // Set array value recursively
    public static function set(&$input, $k, $v = null) {
        $kk = explode('.', $k);
        while (count($kk) > 1) {
            $k = array_shift($kk);
            if (!array_key_exists($k, $input)) {
                $input[$k] = [];
            }
            $input =& $input[$k];
        }
        $input[array_shift($kk)] = $v;
    }

    // Get array value recursively
    public static function get(&$input, $k = null, $fail = false) {
        if ($k === null) return $input;
        $kk = explode('.', $k);
        foreach ($kk as $v) {
            if (!is_array($input) || !array_key_exists($v, $input)) {
                return $fail;
            }
            $input =& $input[$v];
        }
        return $input;
    }

    public static function reset(&$input, $k) {
        $kk = explode('.', $k);
        while (count($k) > 1) {
            $k = array_shift($kk);
            if (array_key_exists($k, $input)) {
                $input =& $input[$k];
            }
        }
        if (is_array($input) && array_key_exists($v = array_shift($kk), $input)) {
            unset($input[$v]);
        }
    }

    public static function extend(&$a, $b) {
        $a = array_replace_recursive($a, $b);
        return $a;
    }

    public static function concat(&$a, $b) {
        $a = array_merge_recursive($a, $b);
        return $a;
    }

    public static function eat($group) {
		$anemon = new Anemon;
        $anemon->bucket = $group;
        return $anemon;
    }

    public function vomit($k = null, $fail = false) {
        return $this->get($this->bucket, $k, $fail);
    }

    public function shake($group) {
        shuffle($group);
        $this->bucket = $group;
        return $this;
    }

    public function sort($order = 'ASC', $key = null, $presv_key = false, $null = X) {
        if (!is_null($key)) {
            $before = $after = [];
            if (!empty($this->bucket)) {
                foreach ($this->bucket as $k => $v) {
                    $v = (array) $v;
                    if (array_key_exists($key, $v)) {
                        $before[$k] = $v[$key];
                    } elseif ($null !== X) {
                        $before[$k] = $null;
                        $this->bucket[$k][$key] = $null;
                    }
                }
                $order === 'ASC' ? asort($before) : arsort($before);
                foreach ($before as $k => $v) {
                    $after[$k] = $this->bucket[$k];
                }
            }
            $this->bucket = $after;
            unset($before, $after);
        } else {
            $this->bucket = (array) $this->bucket;
            $order === 'ASC' ? asort($this->bucket) : arsort($this->bucket);
        }
        if (!$presv_key) {
            $this->bucket = array_values($this->bucket);
        }
        return $this;
    }

    public static function walk($group, $fn = null) {
        if (is_callable($fn)) {
            foreach (a($group) as $k => &$v) {
                $v = is_array($v) ? array_merge($v, self::walk($v, $fn)) : call_user_func($fn, $v, $k);
            }
            unset($v);
            return $group;
        }
        return self::take($group);
    }

    public static function alter($group, $replace = [], $fail = null) {
        // return the `$replace[$group]` value if exist
        // or the `$fail` value if `$replace[$group]` does not exist
        // or the `$group` value if `$fail` is `null`
        return $replace[$group] ?? $fail ?? $group;
    }

    // Move to next array index
    public function next($skip = 0) {
        $this->i = $this->edge($this->i + 1 + $skip, 0, $this->count() - 1);
        return $this;
    }

    // Move to previous array index
    public function prev($skip = 0) {
        $this->i = self::edge($this->i - 1 - $skip, 0, $this->count() - 1);
        return $this;
    }

    // Alias for `Anemon::prev()`
    public function previous(...$lot) {
        return call_user_func_array([$this, 'prev'], $lot);
    }

    // Move to `$index` array index
    public function to($index) {
        $this->i = is_int($index) ? $index : $this->index($index, $index);
        return $this;
    }

    // Insert `$food` before current array index
    public function before($food, $key = null) {
        $key = $key ?? $this->i;
        $this->bucket = array_slice($this->bucket, 0, $this->i, true) + [$key => $food] + array_slice($this->bucket, $this->i, null, true);
        $this->i = self::edge($this->i - 1, 0, $this->count() - 1);
        return $this;
    }

    // Insert `$food` after current array index
    public function after($food, $key = null) {
        $key = $key ?? $this->i + 1;
        $this->bucket = array_slice($this->bucket, 0, $this->i + 1, true) + [$key => $food] + array_slice($this->bucket, $this->i + 1, null, true);
        $this->i = self::edge($this->i + 1, 0, $this->count() - 1);
        return $this;
    }

    // Replace current array index value with `$food`
    public function replace($food) {
        $i = 0;
        foreach ($this->bucket as $k => $v) {
            if ($i === $this->i) {
                $this->bucket[$k] = $food;
                break;
            }
            $i++;
        }
        return $this;
    }

    // Append `$food` to array
    public function append($food, $key = null) {
        $this->i = $this->count() - 1;
        return $this->after($food, $key);
    }

    // Prepend `$food` to array
    public function prepend($food, $key = null) {
        $this->i = 0;
        return $this->before($food, $key);
    }

    // Get first array value
    public function first() {
        $this->i = 0;
        return reset($this->bucket);
    }

    // Get last array value
    public function last() {
        $this->i = $this->count() - 1;
        return end($this->bucket);
    }

    // Get current array index
    public function current() {
        return $this->i;
    }

    // Get selected array value
    public function get($index = null, $fail = false) {
        if ($index !== null) {
            if (is_int($index)) {
                $index = $this->key($index, $index);
            }
            return array_key_exists($index, $this->bucket) ? $this->bucket[$index] : $fail;
        }
        $i = 0;
        foreach ($this->bucket as $k => $v) {
            if ($i === $this->i) {
                return $this->bucket[$k];
            }
            $i++;
        }
    }

    // Get array length
    public function count($deep = false) {
        return count($this->bucket, $deep ? COUNT_RECURSIVE : COUNT_NORMAL);
    }

    // Get array key by position
    public function key($index, $fail = false) {
        $array = array_keys($this->bucket);
        return $array[$index] ?? $fail;
    }

    // Get position by array key
    public function index($key, $fail = false) {
        return array_search($key, array_keys($this->bucket)) ?? $fail;
    }

    // Generate chunk(s) of array
    public function chunk($chunk = 25, $output = null, $fail = []) {
        $chunk = array_chunk($this->bucket, $chunk, true);
        return $output === null ? $chunk : $chunk[$output] ?? $fail;
    }

    public function swap($a, $b = null) {
        return array_column($this->bucket, $a, $b);
    }

}