<?php

class Hook extends Genome {

    protected static $lot = [];

    public static function set($id = null, $fn = null, $stack = null) {
        if (!is_callable($fn)) {
            // `$fn` as `$stack`
            // `$stack` as `$fail`
            return self::exist($id, $fn, $stack ?? false);
        }
        $c = static::class;
        $stack = $stack ?? 10;
        if (!is_array($id)) {
            if (!isset(self::$lot[0][$c][$id][$stack])) {
                if (!isset(self::$lot[1][$c][$id])) {
                    self::$lot[1][$c][$id] = [];
                }
                self::$lot[1][$c][$id][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        } else {
            foreach ($id as $v) {
                self::set($v, $fn, $stack);
            }
        }
        return new static;
    }

    public static function reset($id = null, $stack = null) {
        if (!is_array($id)) {
            $c = static::class;
            if ($id !== null) {
                self::$lot[0][$c][$id][$stack ?? 10] = self::$lot[1][$c][$id] ?? 1;
                if (isset(self::$lot[1][$c][$id])) {
                    if ($stack !== null) {
                        foreach (self::$lot[1][$c][$id] as $k => $v) {
                            if (
                                // eject hook by function name
                                $v['fn'] === $stack ||
                                // eject hook by function stack
                                is_numeric($stack) && $v['stack'] === (float) $stack
                            ) {
                                unset(self::$lot[1][$c][$id][$k]);
                            }
                        }
                    } else {
                        unset(self::$lot[1][$c][$id]);
                    }
                }
            } else {
                self::$lot[1][$c] = [];
            }
        } else {
            foreach ($id as $v) {
                self::reset($v, $stack);
            }
        }
        return new static;
    }

    public static function get($id = null, $fail = false) {
        $c = static::class;
        if ($id === null) {
            return !empty(self::$lot[1][$c]) ? self::$lot[1][$c] : $fail;
        }
        return self::$lot[1][$c][$id] ?? $fail;
    }

    public static function exist($id = null, $stack = null, $fail = false) {
        $c = static::class;
        if ($id === null) {
            return !empty(self::$lot[1][$c]) ? self::$lot[1][$c] : $fail;
        } elseif ($stack === null) {
            return !empty(self::$lot[1][$c][$id]) ? self::$lot[1][$c][$id] : $fail;
        }
        return self::$lot[1][$c][$id][$stack] ?? $fail;
    }

    public static function fire($id, $lot = null) {
        $c = static::class;
        if (!is_array($lot) || !isset($lot[0])) {
            $lot = [$lot];
        }
        if (!is_array($id)) {
            if (!isset(self::$lot[1][$c][$id])) {
                self::$lot[1][$c][$id] = [];
                return $lot[0];
            }
            $hooks = Anemon::eat(self::$lot[1][$c][$id])->sort('ASC', 'stack')->vomit();
            foreach ($hooks as $v) {
                $lot[0] = call_user_func_array($v['fn'], $lot);
            }
        } else {
            $a = func_get_args();
            foreach ($id as $v) {
                $a[0] = $v;
                $lot[0] = call_user_func_array('self::fire', $a);
            }
        }
        return $lot[0];
    }

    public static function NS(...$lot) {
        if (strpos($lot[0], ':') !== false) {
            $s = explode(':', $lot[0], 2);
            $lot[0] = [$lot[0], $s[1]];
        }
        return call_user_func_array('self::fire', $lot);
    }

}