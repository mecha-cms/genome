<?php

class Hook extends Genome {

    protected static $lot = [];

    public static function set($id = null, $fn = null, $stack = null) {
        $c = static::class;
        $stack = $stack ?: 10;
        if (!is_array($id)) {
            if (!isset(self::$lot[1][$c][$id])) {
                self::$lot[1][$c][$id] = [];
            }
            self::$lot[1][$c][$id][] = [
                'fn' => $fn,
                'stack' => (float) $stack
            ];
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
            if (isset($id)) {
                self::$lot[0][$c][$id][$stack ?: 10] = isset(self::$lot[1][$c][$id]) ? self::$lot[1][$c][$id] : 1;
                if (isset(self::$lot[1][$c][$id])) {
                    if (isset($stack)) {
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
        if (!isset($id)) {
            return !empty(self::$lot[1][$c]) ? self::$lot[1][$c] : $fail;
        }
        return !empty(self::$lot[1][$c][$id]) ? self::$lot[1][$c][$id] : $fail;
    }

    public static function fire($id, $lot = null) {
        $c = static::class;
        if (!is_array($lot) || !array_key_exists(0, $lot)) {
            $lot = [$lot];
        }
        if (!is_array($id)) {
            if (!isset(self::$lot[1][$c][$id])) {
                self::$lot[1][$c][$id] = [];
                return $lot[0];
            }
            $hooks = Anemon::eat(self::$lot[1][$c][$id])->sort(1, 'stack')->vomit();
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
        if (strpos($lot[0], '.') !== false) {
            $a = explode('.', $lot[0]);
            $lot[0] = [];
            while (isset($a[0])) {
                $lot[0][] = implode('.', $a);
                array_shift($a);
                $lot[1] = call_user_func_array('self::fire', $lot);
            }
            return $lot[1];
        }
        return call_user_func_array('self::fire', $lot);
    }

}