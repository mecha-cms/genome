<?php

final class Route extends Genome {

    private static $lot;
    private static $over;

    public static function fire(string $id, array $lot = []) {
        if (isset(self::$lot[1][$id])) {
            // Loading hook(s)…
            if (isset(self::$over[1][$id])) {
                $fn = Anemon::eat(self::$over[1][$id])->sort([1, 'stack']);
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

    public static function is($id) {
        if (is_array($id)) {
            $out = [];
            foreach ($id as $v) {
                $out[$v] = self::is($v);
            }
            return $out;
        }
        $path = rtrim($GLOBALS['URL']['path'] . '/' . $GLOBALS['URL']['i'], '/');
        if (strpos($id, '(') === false && strpos($id, "\\") === false) {
            return $path === $id ? [
                'pattern' => $id,
                'path' => $path,
                'lot' => []
            ] : false;
        } else if (preg_match('#^' . $id . '$#', $path, $m)) {
            array_shift($m); // Remove the first match
            return [
                'pattern' => $id,
                'path' => $path,
                'lot' => e($m)
            ];
        }
        return false;
    }

    public static function lot($id, callable $fn = null, float $stack = 10) {
        if (is_array($id)) {
            if (!isset($fn)) {
                $out = [];
                foreach ($id as $v) {
                    $out[$v] = self::lot($v);
                }
                return $out;
            }
            $i = 0;
            foreach ($id as $v) {
                self::lot($v, $fn, $stack + $i);
                $i += .1;
            }
        } else {
            if (!isset($fn)) {
                return self::$over[1][$id] ?? null;
            }
            if (!isset(self::$over[0][$id])) {
                self::$over[1][$id][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        }
    }

    public static function reset($id = null) {
        if (is_array($id)) {
            foreach ($id as $v) {
                self::reset($v);
            }
        } else if (isset($id)) {
            self::$lot[0][$id] = self::$lot[1][$id] ?? 1;
            unset(self::$lot[1][$id]);
        } else {
            self::$lot[1] = [];
        }
    }

    public static function set($id, callable $fn = null, float $stack = 10) {
        if (is_array($id)) {
            $i = 0;
            foreach ($id as $v) {
                self::set($v, $fn, $stack + $i);
                $i += .1;
            }
        } else {
            if (!isset(self::$lot[0][$id])) {
                self::$lot[1][$id] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        }
    }

    public static function start() {
        $id = rtrim($GLOBALS['URL']['path'] . '/' . $GLOBALS['URL']['i'], '/');
        $any = Anemon::eat(self::$lot[1] ?? [])->sort([1, 'stack'], true);
        foreach ($any as $k => $v) {
            // If matched with the URL path, then …
            if (false !== ($route = self::is($k))) {
                // Loading hook(s)…
                if (isset(self::$over[1][$k])) {
                    $fn = Anemon::eat(self::$over[1][$k])->sort([1, 'stack']);
                    foreach ($fn as $f) {
                        if ($rr = call_user_func($f['fn'], ...$route['lot'])) {
                            break;
                        }
                    }
                }
                // Passed!
                $r = $rr ?? call_user_func($v['fn'], ...$route['lot']);
                break;
            }
        }
        echo $r ?? "";
    }

}