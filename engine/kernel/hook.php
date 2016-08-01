<?php

class Hook extends __ {

    protected static $lot = [];

    public static function set($id, $fn, $stack = null) {
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
    }

    public static function fire(...$a) {
        if (!is_array($a[0])) {
            $c = static::class;
            $id = array_shift($a);
            $lot = array_shift($a);
            $r = array_shift($a);
            if (!isset(self::$lot[1][$c][$id])) {
                self::$lot[1][$c][$id] = [];
                return $r;
            }
            $signal = Anemon::eat(self::$lot[1][$c][$id])->sort('ASC', 'stack')->vomit();
            foreach ($signal as $v) {
                $r = call_user_func_array($v['fn'], $lot);
            }
        } else {
            foreach ($id as $v) {
                $a[0] = $v;
                $r = call_user_func_array('self::fire', $lot);
            }
        }
        return $r;
    }

    public static function block($id = null, $stack = null) {
        if (!is_array($id)) {
            $c = static::class;
            if ($id !== null) {
                self::$lot[0][$c][$id][$stack ?? 10] = self::$lot[1][$c][$id] ?? 1;
                if (isset(self::$lot[1][$c][$id])) {
                    if ($stack !== null) {
                        foreach (self::$lot[1][$c][$id] as $k => $v) {
                            if (
                                // eject weapon by function name
                                $v['fn'] === $stack ||
                                // eject weapon by function stack
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
                self::block($v, $stack);
            }
        }
    }

    public static function get($id = null, $fail = false) {
        $c = static::class;
        if ($id === null) {
            return !empty(self::$lot[1][$c]) ? self::$lot[1][$c] : $fail;
        }
        return self::$lot[1][$c][$id] ?? $fail;
    }

    public static function blocked($id = null, $stack = null, $fail = false) {
        $c = static::class;
        $stack = $stack ?? 10;
        if ($id === null) {
            return !empty(self::$lot[0][$c]) ? self::$lot[0][$c] : $fail;
        } elseif ($stack === null) {
            return !empty(self::$lot[0][$c][$id]) ? self::$lot[0][$c][$id] : $fail;
        }
        return self::$lot[0][$c][$id][$stack] ?? $fail;
    }

    public static function NS(...$lot) {
        if(strpos($lot[0], ':') !== false) {
            $s = explode(':', $lot[0], 2);
            $lot[0] = [$lot[0], $s[1]];
        }
        return call_user_func_array('self::fire', $lot);
    }

}