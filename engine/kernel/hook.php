<?php

class Hook extends Genome {

    protected static $lot_ = [];

    protected static function set_($id, $fn, $stack = null) {
        $c = static::class;
        $stack = $stack ?? 10;
        if (!is_array($id)) {
            if (!isset(self::$lot_[0][$c][$id][$stack])) {
                if (!isset(self::$lot_[1][$c][$id])) {
                    self::$lot_[1][$c][$id] = [];
                }
                self::$lot_[1][$c][$id][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        } else {
            foreach ($id as $v) {
                self::set_($v, $fn, $stack);
            }
        }
        return new static;
    }

    protected static function fire_($id, $lot = null) {
        $c = static::class;
        if (!is_array($lot) || !isset($lot[0])) {
            $lot = [$lot];
        }
        if (!is_array($id)) {
            if (!isset(self::$lot_[1][$c][$id])) {
                self::$lot_[1][$c][$id] = [];
                return $lot[0];
            }
            $hooks = Anemon::eat(self::$lot_[1][$c][$id])->sort('ASC', 'stack')->vomit();
            foreach ($hooks as $v) {
                $lot[0] = call_user_func_array($v['fn'], $lot);
            }
        } else {
            $a = func_get_args();
            foreach ($id as $v) {
                $a[0] = $v;
                $lot[0] = call_user_func_array('self::fire_', $a);
            }
        }
        return $lot[0];
    }

    protected static function block_($id = null, $stack = null) {
        if (!is_array($id)) {
            $c = static::class;
            if ($id !== null) {
                self::$lot_[0][$c][$id][$stack ?? 10] = self::$lot_[1][$c][$id] ?? 1;
                if (isset(self::$lot_[1][$c][$id])) {
                    if ($stack !== null) {
                        foreach (self::$lot_[1][$c][$id] as $k => $v) {
                            if (
                                // eject hook by function name
                                $v['fn'] === $stack ||
                                // eject hook by function stack
                                is_numeric($stack) && $v['stack'] === (float) $stack
                            ) {
                                unset(self::$lot_[1][$c][$id][$k]);
                            }
                        }
                    } else {
                        unset(self::$lot_[1][$c][$id]);
                    }
                }
            } else {
                self::$lot_[1][$c] = [];
            }
        } else {
            foreach ($id as $v) {
                self::block_($v, $stack);
            }
        }
        return new static;
    }

    protected static function get_($id = null, $fail = false) {
        $c = static::class;
        if ($id === null) {
            return !empty(self::$lot_[1][$c]) ? self::$lot_[1][$c] : $fail;
        }
        return self::$lot_[1][$c][$id] ?? $fail;
    }

    protected static function blocked_($id = null, $stack = null, $fail = false) {
        $c = static::class;
        if ($id === null) {
            return !empty(self::$lot_[0][$c]) ? self::$lot_[0][$c] : $fail;
        } elseif ($stack === null) {
            return !empty(self::$lot_[0][$c][$id]) ? self::$lot_[0][$c][$id] : $fail;
        }
        return self::$lot_[0][$c][$id][$stack] ?? $fail;
    }

    protected static function NS_(...$lot) {
        if (strpos($lot[0], ':') !== false) {
            $s = explode(':', $lot[0], 2);
            $lot[0] = [$lot[0], $s[1]];
        }
        return call_user_func_array('self::fire_', $lot);
    }

}