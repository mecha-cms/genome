<?php

class Filter extends __ {

    protected static $lot = [];
    protected static $lot_x = [];

    public static function add($name, $fn, $stack = null) {
        $c = static::class;
        $stack = $stack ?? 10;
        if (is_string($name)) {
            if (!isset(self::$lot_x[$c][$name][$stack])) {
                if (!isset(self::$lot[$c][$name])) {
                    self::$lot[$c][$name] = [];
                }
                self::$lot[$c][$name][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        } else {
            foreach ($name as $v) {
                self::add($v, $fn, $stack);
            }
        }
    }

    public static function apply($name, $target, $lot = []) {
        if (is_string($name)) {
            $c = static::class;
            if (!isset(self::$lot[$c][$name])) {
                self::$lot[$c][$name] = [];
                return $target;
            }
            $s = Group::take(self::$lot[$c][$name])->order('ASC', 'stack')->give();
            foreach ($s as $k => $v) {
                $lot[0] = $target;
                $target = call_user_func_array($v['fn'], $lot);
            }
        } else {
            foreach (array_reverse($name) as $v) {
                $lot[0] = $v;
                $lot[1] = $target;
                $target = call_user_func_array('self::apply', $lot);
            }
        }
        return $target;
    }

    public static function remove($name = null, $stack = null) {
        if (is_string($name)) {
            $c = static::class;
            if ($name !== null) {
                self::$lot_x[$c][$name][$stack ?? 10] = self::$lot[$c][$name] ?? 1;
                if (isset(self::$lot[$c][$name])) {
                    if ($stack !== null) {
                        foreach (self::$lot[$c][$name] as $k => $v) {
                            if (
                                // remove filter by function name
                                $v['fn'] === $stack ||
                                // remove filter by function stack
                                is_numeric($stack) && $v['stack'] === (float) $stack
                            ) {
                                unset(self::$lot[$c][$name][$k]);
                            }
                        }
                    } else {
                        unset(self::$lot[$c][$name]);
                    }
                }
            } else {
                self::$lot[$c] = [];
            }
        } else {
            foreach ($name as $v) {
                self::remove($v, $stack);
            }
        }
    }

    public static function exist($name = null, $fail = false) {
        $c = static::class;
        if ($name === null) {
            return !empty(self::$lot[$c]) ? self::$lot[$c] : $fail;
        }
        return self::$lot[$c][$name] ?? $fail;
    }

    public static function removed($name = null, $stack = null, $fail = false) {
        $c = static::class;
        $stack = $stack ?? 10;
        if ($name === null) {
            return !empty(self::$lot_x[$c]) ? self::$lot_x[$c] : $fail;
        } else if ($stack === null) {
            return !empty(self::$lot_x[$c][$name]) ? self::$lot_x[$c][$name] : $fail;
        }
        return self::$lot_x[$c][$name][$stack] ?? $fail;
    }

}