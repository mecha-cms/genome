<?php

class Hook extends Genome {

    protected static $i = [];
    protected static $lot = [];

    public static function set($id = null, $fn = null, $stack = null, $i = 100) {
        $c = static::class;
        $stack = isset($stack) ? $stack : 10;
        if (!is_array($id)) {
            if (!isset(self::$lot[0][$c][$id][$stack])) {
                if (!isset(self::$lot[1][$c][$id])) {
                    self::$lot[1][$c][$id] = [];
                }
                self::$lot[1][$c][$id][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack,
                    'i' => $i
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
        $c = static::class;
        if (!is_array($id)) {
            if (isset($id)) {
                self::$lot[0][$c][$id][isset($stack) ? $stack : 10] = isset(self::$lot[1][$c][$id]) ? self::$lot[1][$c][$id] : 1;
                if (isset(self::$lot[1][$c][$id])) {
                    if (isset($stack)) {
                        foreach (self::$lot[1][$c][$id] as $k => $v) {
                            if (
                                // Eject hook by function name
                                $v['fn'] === $stack ||
                                // Eject hook by function stack
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
        if (isset($id)) {
            return !empty(self::$lot[1][$c][$id]) ? self::$lot[1][$c][$id] : $fail;
        }
        return !empty(self::$lot[1][$c]) ? self::$lot[1][$c] : $fail;
    }

    public static function fire($id, array $lot = []) {
        $c = static::class;
        if (!array_key_exists(0, $lot)) {
            $lot = [null];
        }
        if (!is_array($id)) {
            if (!isset(self::$lot[1][$c][$id])) {
                self::$lot[1][$c][$id] = [];
                return $lot[0];
            }
            $hooks = Anemon::eat(self::$lot[1][$c][$id])->sort([1, 'stack'])->vomit();
            foreach ($hooks as $v) {
                if (!is_callable($v['fn'])) {
                    continue;
                }
                if (is_string($v['fn'])) {
                    if (!isset(self::$i[$c][$id][$v['fn']])) {
                        self::$i[$c][$id][$v['fn']] = 0;
                    } else {
                        if (self::$i[$c][$id][$v['fn']] > $v['i']) {
                            continue;
                        }
                        ++self::$i[$c][$id][$v['fn']];
                    }
                }
                if (($s = call_user_func($v['fn'], ...$lot)) !== null) {
                    $lot[0] = $s;
                }
            }
        } else {
            foreach ($id as $v) {
                if (($s = self::fire($v, $lot)) !== null) {
                    $lot[0] = $s;
                }
            }
        }
        return $lot[0];
    }

    public static function NS(...$lot) {
        if (strpos($lot[0], '.') !== false) {
            foreach (Anemon::step($lot[0], '.', -1) as $v) {
                $lot[0] = $v;
                $lot[1] = [self::fire(...$lot)];
            }
            return $lot[1][0];
        }
        return self::fire(...$lot);
    }

}