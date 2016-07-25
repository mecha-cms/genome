<?php

class Group extends __ {

    protected $bucket = [];

    // Set array value recursively
    public static function S(&$input, $k, $v = null) {
        $k = explode('.', $k);
        while (count($k) > 1) {
            $k = array_shift($k);
            if (!array_key_exists($k, $input)) {
                $input[$k] = [];
            }
            $input =& $input[$k];
        }
        $input[array_shift($k)] = $v;
    }

    // Get array value recursively
    public static function G(&$input, $k = null, $fail = false) {
        if ($k === null) return $input;
        $k = explode('.', $k);
        foreach ($k as $v) {
            if (!is_array($input) || !array_key_exists($v, $input)) {
                return $fail;
            }
            $input =& $input[$v];
        }
        return $input;
    }

    public static function R(&$input, $k) {
        $k = explode('.', $k);
        while (count($k) > 1) {
            $k = array_shift($k);
            if (array_key_exists($k, $input)) {
                $input =& $input[$k];
            }
        }
        if (is_array($input) && array_key_exists($v = array_shift($k), $input)) {
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

    public function take($group) {
        $this->bucket = $group;
        return $this;
    }

    public function give($k = null, $fail = false) {
        return $this->bucket;
    }

    public function shake() {
        shuffle($this->bucket);
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
                    } else if ($null !== X) {
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

    public function walk($group, $fn = null) {
        if (is_callable($fn)) {
            foreach (a($group) as $k => &$v) {
                $v = is_array($v) ? array_merge($v, $this->walk($v, $fn)) : call_user_func($fn, $v, $k);
            }
            unset($v);
            return $group;
        }
        return $this->take($group);
    }

    public function has($s, $x = X) {
        return strpos(X . implode(X . $this->bucket . X, X . $s . X) !== false;
    }

    public static function alter($group, $replace = [], $fail = null) {
        // return the `$replace[$group]` value if exist
        // or the `$fail` value if `$replace[$group]` does not exist
        // or the `$group` value if `$fail` is `null`
        return $replace[$group] ?? $fail ?? $group;
    }

}