<?php

class Route extends Genome {

    protected static $lot = [];
    protected static $lot_o = [];

    public static function set($id = null, callable $fn = null, float $stack = null, $pattern = false) {
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = URL::short($v, false);
            if (!isset(self::$lot[0][$v])) {
                self::$lot[1][$v] = [
                    'fn' => $fn,
                    'stack' => (float) (($stack[$k] ?? (end($stack) !== false ? end($stack) : 10)) + $i),
                    'is' => ['pattern' => $pattern]
                ];
                $i += .1;
            }
        }
        return true;
    }

    public static function reset($id = null) {
        if (isset($id)) {
            foreach ((array) $id as $v) {
                $v = URL::short($v, false);
                self::$lot[0][$v] = self::$lot[1][$v] ?? 1;
                unset(self::$lot[1][$v]);
            }
        } else {
            self::$lot = [];
        }
        return true;
    }

    public static function lot($id, callable $fn = null, float $stack = null, $pattern = false) {
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = URL::short($v, false);
            if (!isset(self::$lot_o[0][$v])) {
                self::$lot_o[1][$v][] = [
                    'fn' => $fn,
                    'stack' => (float) (($stack[$k] ?? (end($stack) !== false ? end($stack) : 10)) + $i),
                    'is' => ['pattern' => $pattern]
                ];
                $i += .1;
            }
        }
        return true;
    }

    public static function pattern($pattern, callable $fn = null, float $stack = null) {
        return self::set($pattern, $fn, $stack, true);
    }

    public static function is(string $id, $fail = false, $pattern = false) {
        $id = URL::short($id, false);
        $path = rtrim($GLOBALS['URL']['path'] . '/' . $GLOBALS['URL']['i'], '/');
        if (strpos($id, '%') === false) {
            return $path === $id ? [
                'pattern' => $id,
                'path' => $path,
                'lot' => []
            ] : $fail;
        }
        if (preg_match($pattern ? $id : '#^' . format($id, '\/\n', '#', false) . '$#', $path, $m)) {
            array_shift($m);
            return [
                'pattern' => $id,
                'path' => $path,
                'lot' => e($m)
            ];
        }
        return $fail;
    }

    public static function get(string $id = null, $fail = false) {
        if (isset($id)) {
            return self::$lot[1][$id] ?? $fail ?: $fail;
        }
        return self::$lot[1] ?? $fail ?: $fail;
    }

    public static function has(string $id = null, $stack = null, $fail = false) {
        if (isset($id)) {
            if (isset($stack)) {
                $routes = [];
                foreach (self::$lot_o[1][$id] as $v) {
                    if (
                        $v['fn'] === $stack || // `$stack` as `$fn`
                        is_numeric($stack) && $v['stack'] === (float) $stack
                    ) {
                        $routes[] = $v;
                    }
                }
                return $routes ?? $fail ?: $fail;
            } else {
                return self::$lot_o[1][$id] ?? $fail ?: $fail;
            }
        }
        return self::$lot_o[1] ?? $fail ?: $fail;
    }

    public static function fire(string $id = null, array $lot = []) {
        $s = c2f($c = static::class, '_', '/') . '.';
        if (isset($id)) {
            $id = URL::short($id, false);
            if (isset(self::$lot[1][$id])) {
                return call_user_func(self::$lot[1][$id]['fn'], ...$lot);
            }
        } else {
            $id = rtrim($GLOBALS['URL']['path'] . '/' . $GLOBALS['URL']['i'], '/');
            $over = false;
            if (isset(self::$lot[1][$id])) {
                // Loading cargo(s)…
                if (isset(self::$lot_o[1][$id])) {
                    $fn = Anemon::eat(self::$lot_o[1][$id])->sort([1, 'stack']);
                    foreach ($fn as $v) {
                        if ($r = call_user_func($v['fn'], ...$lot)) {
                            $over = $r;
                            break;
                        }
                    }
                }
                // Passed!
                Hook::fire($s . 'enter', [self::$lot[1][$id], null], null, $c);
                $r = $over ?: call_user_func(self::$lot[1][$id]['fn'], ...$lot);
                Hook::fire($s . 'exit', [self::$lot[1][$id], null], null, $c);
                return $r;
            } else {
                $routes = Anemon::eat(self::$lot[1] ?? [])->sort([1, 'stack'], true);
                foreach ($routes as $k => $v) {
                    // If matched with the URL path, then …
                    if (($route = self::is($k, false, $v['is']['pattern'])) !== false) {
                        // Loading hook(s)…
                        if (isset(self::$lot_o[1][$k])) {
                            $fn = Anemon::eat(self::$lot_o[1][$k])->sort([1, 'stack']);
                            foreach ($fn as $f) {
                                if ($r = call_user_func($f['fn'], ...$route['lot'])) {
                                    $over = $r;
                                    break;
                                }
                            }
                        }
                        // Passed!
                        Hook::fire($s . 'enter', [self::$lot[1][$k], null], null, $c);
                        $r = $over ?: call_user_func($v['fn'], ...$route['lot']);
                        Hook::fire($s . 'exit', [self::$lot[1][$k], null], null, $c);
                        return $r;
                    }
                }
            }
        }
        return null;
    }

}