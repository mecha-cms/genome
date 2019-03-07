<?php

final class Route extends Genome {

    private static $hook;
    private static $lot;

    public static function fire(string $id, array $lot = []) {
        $id = trim($id, '/');
        if (isset(self::$lot[1][$id])) {
            // Loading hook(s)…
            if (isset(self::$hook[1][$id])) {
                $fn = Anemon::eat(self::$hook[1][$id])->sort([1, 'stack']);
                foreach ($fn as $v) {
                    if ($r = call_user_func($v['fn'], ...$lot)) {
                        break;
                    }
                }
            }
            return $r ?? call_user_func(self::$lot[1][$id]['fn'], ...$lot);
        }
    }

    public static function get(string $id = null) {
        if (isset($id)) {
            return self::$lot[1][$id] ?? null;
        }
        return self::$lot[1] ?? [];
    }

    public static function has(string $id = null, $stack = null) {
        if (isset($id)) {
            if (isset($stack)) {
                $any = [];
                foreach (self::$hook[1][$id] as $v) {
                    if (
                        $v['fn'] === $stack || // `$stack` as `$fn`
                        is_numeric($stack) && $v['stack'] === (float) $stack
                    ) {
                        $any[] = $v;
                    }
                }
                return $any;
            } else {
                return self::$hook[1][$id] ?? null;
            }
        }
        return self::$hook[1] ?? [];
    }

    public static function is(string $id, $pattern = false) {
        $id = trim($id, '/');
        $path = rtrim($GLOBALS['URL']['path'] . '/' . $GLOBALS['URL']['i'], '/');
        if (strpos($id, '%') === false) {
            return $path === $id ? [
                'pattern' => $id,
                'path' => $path,
                'lot' => []
            ] : false;
        }
        if (preg_match($pattern ? $id : '#^' . format($id, '\/\n', '#', false) . '$#', $path, $m)) {
            array_shift($m); // Remove the first match
            return [
                'pattern' => $id,
                'path' => $path,
                'lot' => e($m)
            ];
        }
        return false;
    }

    public static function lot($id, callable $fn = null, float $stack = null, $pattern = false) {
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = trim($v, '/');
            if (!isset(self::$hook[0][$v])) {
                self::$hook[1][$v][] = [
                    'fn' => $fn,
                    'stack' => (float) (($stack[$k] ?? (end($stack) !== false ? end($stack) : 10)) + $i),
                    'is' => ['pattern' => $pattern]
                ];
                $i += .1;
            }
        }
    }

    public static function pattern($pattern, callable $fn = null, float $stack = null) {
        return self::set($pattern, $fn, $stack, true);
    }

    public static function reset($id = null) {
        if (isset($id)) {
            foreach ((array) $id as $v) {
                $v = trim($v, '/');
                self::$lot[0][$v] = self::$lot[1][$v] ?? 1;
                unset(self::$lot[1][$v]);
            }
        } else {
            self::$lot = [];
        }
    }

    public static function set($id = null, callable $fn = null, float $stack = null, $pattern = false) {
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = trim($v, '/');
            if (!isset(self::$lot[0][$v])) {
                self::$lot[1][$v] = [
                    'fn' => $fn,
                    'stack' => (float) (($stack[$k] ?? (end($stack) !== false ? end($stack) : 10)) + $i),
                    'is' => ['pattern' => $pattern]
                ];
                $i += .1;
            }
        }
    }

    public static function start() {
        $id = rtrim($GLOBALS['URL']['path'] . '/' . $GLOBALS['URL']['i'], '/');
        if (!$r = self::fire($id)) {
            $any = Anemon::eat(self::$lot[1] ?? [])->sort([1, 'stack'], true);
            foreach ($any as $k => $v) {
                // If matched with the URL path, then …
                if (false !== ($m = self::is($k, false, $v['is']['pattern']))) {
                    // Loading hook(s)…
                    if (isset(self::$hook[1][$k])) {
                        $fn = Anemon::eat(self::$hook[1][$k])->sort([1, 'stack']);
                        foreach ($fn as $f) {
                            if ($rr = call_user_func($f['fn'], ...$m['lot'])) {
                                break;
                            }
                        }
                    }
                    // Passed!
                    $r = $rr ?? call_user_func($v['fn'], ...$m['lot']);
                    break;
                }
            }
        }
        echo $r;
    }

}