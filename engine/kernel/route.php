<?php

class Route extends Genome {

    public static $lot = [];
    public static $lot_o = [];

    // Pattern as regular expression
    protected static function _v($s) {
        if (strpos($s, '%[') !== false) {
            $s = preg_replace_callback('#%\[(.*?)\]#', function($m) {
                $m[1] = str_replace(['\,', ','], [X, '|'], $m[1]);
                return '(' . $m[1] . ')';
            }, $s);
        }
        return str_replace([
            '\(', '\|', '\)',
            '%s', // any string except `/`
            '%i', // any string numbers
             '%', // any string includes `/`
              X
        ], [
            '(', '|', ')',
            '[^/]+',
            '\d+',
            '.*?',
            ','
        ], x($s, '#'));
    }

    public static function set($id = null, $fn = null, $stack = null, $pattern = false) {
        if (!is_callable($fn)) {
            return self::exist($id, $fn ?? false);
        }
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = URL::short($v, false);
            if (!isset(self::$lot[0][$v])) {
                self::$lot[1][$v] = [
                    'fn' => $fn,
                    'stack' => (float) (($stack[$k] ?? end($stack)) + $i),
                    'is' => ['pattern' => $pattern]
                ];
                $i += .1;
            }
        }
        return true;
    }

    public static function exist($id = null, $fail = false) {
        if ($id !== null) {
            return self::$lot[1][$id] ?? $fail;
        }
        return !empty(self::$lot[1]) ? self::$lot[1] : $fail;
    }

    public static function get(...$lot) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return call_user_func_array('self::set', $lot);
        }
        return false;
    }

    public static function post(...$lot) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return call_user_func_array('self::set', $lot);
        }
        return false;
    }

    protected static function reset_($id) {
        foreach ((array) $id as $v) {
            $v = URL::short($v, false);
            self::$lot[0][$v] = self::$lot[1][$v] ?? 1;
            unset($this->lot[1][$v]);
        }
        return true;
    }

    protected static function reseted_($id = null, $fail = false) {
        if ($id === null) {
            return !empty(self::$lot[0]) ? self::$lot[0] : $fail;
        }
        return self::$lot[0][$id] ?? $fail;
    }

    protected static function hook_($id, $fn = null, $stack = null, $pattern = false) {
        $id = URL::short($id, false);
        $stack = $stack ?? 10;
        if (!isset(self::$lot_o[1][$id])) {
            self::$lot_o[1][$id] = [];
        }
        self::$lot_o[1][$id][] = [
            'fn' => $fn,
            'stack' => (float) $stack,
            'is' => ['pattern' => $pattern]
        ];
        return true;
    }

    protected static function hooked_($id = null, $stack = null, $fail = false) {
        if ($id !== null) {
            if ($stack !== null) {
                $routes = [];
                foreach (self::$lot_o[1][$id] as $v) {
                    if (
                        $v['fn'] === $stack ||
                        is_numeric($stack) && $v['stack'] === (float) $stack
                    ) {
                        $routes[] = $v;
                    }
                }
                return !empty($routes) ? $routes : $fail;
            } else {
                return self::$lot_o[1][$id] ?? $fail;
            }
        }
        return !empty(self::$lot_o[1]) ? self::$lot_o[1] : $fail;
    }

    protected static function pattern_($pattern, $fn = false, $stack = null) {
        if (!is_callable($fn)) {
            $path = URL::path();
            if (preg_match($pattern, $path, $m)) {
                array_shift($m);
                return [
                    'id' => $pattern,
                    'path' => $path,
                    'lot' => e($m)
                ];
            }
            return $fn;
        }
        return self::set_($pattern, $fn, $stack, true);
    }

    protected static function is_($id, $fail = false) {
        $id = URL::short($id, false);
        $path = URL::path();
        if (strpos($id, '%') === false) {
            return $path === $id ? [
                'id' => $id,
                'path' => $path,
                'lot' => []
            ] : $fail;
        }
        if (preg_match('#^' . self::_v($id) . '$#', $path, $m)) {
            array_shift($m);
            return [
                'id' => $id,
                'path' => $path,
                'lot' => e($m)
            ];
        }
        return $fail;
    }

    protected static function fire_($id = null, $lot = []) {
        if ($id !== null) {
            $id = URL::short($id, false);
            if (isset(self::$lot[1][$id])) {
                call_user_func_array(self::$lot[1][$id]['fn'], $lot);
                return true;
            }
        } else {
            $id = URL::path();
            if (isset(self::$lot[1][$id])) {
                // Loading cargo(s) ...
                if (isset(self::$lot_o[1][$id])) {
                    $fn = Anemon::eat(self::$lot_o[1][$id])->sort('ASC', 'stack')->vomit();
                    foreach ($fn as $v) {
                        call_user_func_array($v['fn'], $lot);
                    }
                }
                // Passed!
                call_user_func_array(self::$lot[1][$id]['fn'], $lot);
                return true;
            } else {
                $routes = Anemon::eat(self::$lot[1] ?? [])->sort('ASC', 'stack', true)->vomit();
                foreach ($routes as $k => $v) {
                    // If matched with the URL path
                    if ($route = self::is_($k)) {
                        // Loading hook(s) ...
                        if (isset(self::$lot_o[1][$k])) {
                            $fn = Anemon::eat(self::$lot_o[1][$k])->sort('ASC', 'stack')->vomit();
                            foreach ($fn as $f) {
                                call_user_func_array($f['fn'], $route['lot']);
                            }
                        }
                        // Passed!
                        call_user_func_array($v['fn'], $route['lot']);
                        return true;
                    }
                }
            }
        }
        return null;
    }

}