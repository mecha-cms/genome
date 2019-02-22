<?php

class Hook extends Genome {

    protected static $lot;
    protected static $current;

    public static function is(string $id) {
        return self::$current[static::class] === $id;
    }

    public static function fire($id, array $lot = [], $that = null, string $scope = null) {
        $c = static::class;
        if (!array_key_exists(0, $lot)) {
            $lot = [null];
        }
        if (!is_array($id)) {
            self::$current[$c] = $id;
            if (!isset(self::$lot[1][$c][$id])) {
                self::$lot[1][$c][$id] = [];
                return $lot[0];
            }
            $hooks = Anemon::eat(self::$lot[1][$c][$id])->sort([1, 'stack']);
            foreach ($hooks as $v) {
                if (null !== ($r = fn($v['fn'], $lot, $that, $scope))) {
                    $lot[0] = $r;
                }
            }
        } else {
            foreach ($id as $v) {
                self::$current[$c] = $v;
                if (null !== ($r = self::fire($v, $lot, $that, $scope))) {
                    $lot[0] = $r;
                }
            }
        }
        return $lot[0];
    }

    public static function get(string $id = null) {
        $c = static::class;
        if (isset($id)) {
            return self::$lot[1][$c][$id] ?? null;
        }
        return self::$lot[1][$c] ?? [];
    }

    public static function reset($id = null, $x = null) {
        $c = static::class;
        if (!is_array($id)) {
            if (isset($id)) {
                self::$lot[0][$c][$id][$x ?? 10] = self::$lot[1][$c][$id] ?? 1;
                if (isset(self::$lot[1][$c][$id])) {
                    if (isset($x)) {
                        foreach (self::$lot[1][$c][$id] as $k => $v) {
                            if (
                                // Eject hook by function name
                                $v['fn'] === $x ||
                                // Eject hook by function stack
                                is_numeric($x) && $v['stack'] === (float) $x
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
    }

    public static function set($id = null, callable $fn, float $stack = null) {
        $c = static::class;
        $stack = $stack ?? 10;
        if (is_array($id)) {
            foreach ($id as $v) {
                self::set((string) $v, $fn, $stack);
            }
        } else {
            if (!isset(self::$lot[0][$c][$id][$stack])) {
                if (!isset(self::$lot[1][$c][$id])) {
                    self::$lot[1][$c][$id] = [];
                }
                self::$lot[1][$c][$id][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        }
    }

}