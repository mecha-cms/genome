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
            '%s', // any string excludes `/`
            '%i', // any string number(s)
            '%f', // any string number(s) includes float(s)
             '%', // any string includes `/`
              X
        ], [
            '(', '|', ')',
            '([^/]+)?',
            '(\-?\d+)?',
            '(\-?(?:(?:\d+)?\.)?\d+)?',
            '(.*?)?',
            ','
        ], x($s, '#'));
    }

    public static function set($id = null, $fn = null, $stack = null, $pattern = false) {
        if (!is_callable($fn)) {
            return self::exist($id, isset($fn) ? $fn : false);
        }
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = URL::short($v, false);
            if (!isset(self::$lot[0][$v])) {
                self::$lot[1][$v] = [
                    'fn' => $fn,
                    'stack' => (float) ((isset($stack[$k]) ? $stack[$k] : end($stack)) + $i),
                    'is' => ['pattern' => $pattern]
                ];
                $i += .1;
            }
        }
        return true;
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

    public static function reset($id) {
        foreach ((array) $id as $v) {
            $v = URL::short($v, false);
            self::$lot[0][$v] = isset(self::$lot[1][$v]) ? self::$lot[1][$v] : 1;
            unset($this->lot[1][$v]);
        }
        return true;
    }

    public static function hook($id, $fn = null, $stack = null, $pattern = false) {
        $id = URL::short($id, false);
        $stack = isset($stack) ? $stack : 10;
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

    public static function pattern($pattern, $fn = false, $stack = null) {
        if (!is_callable($fn)) {
            $path = URL::path();
            if (preg_match($pattern, $path, $m)) {
                array_shift($m);
                return [
                    'pattern' => $pattern,
                    'path' => $path,
                    'lot' => e($m)
                ];
            }
            return $fn;
        }
        return self::set($pattern, $fn, $stack, true);
    }

    public static function is($id, $fail = false) {
        $id = URL::short($id, false);
        $path = URL::path();
        if (strpos($id, '%') === false) {
            return $path === $id ? [
                'pattern' => $id,
                'path' => $path,
                'lot' => []
            ] : $fail;
        }
        if (preg_match('#^' . self::_v($id) . '$#', $path, $m)) {
            array_shift($m);
            return [
                'pattern' => $id,
                'path' => $path,
                'lot' => e($m)
            ];
        }
        return $fail;
    }

    public static function exist($id = null, $fail = false) {
        if (isset($id)) {
            return isset(self::$lot[1][$id]) ? self::$lot[1][$id] : $fail;
        }
        return !empty(self::$lot[1]) ? self::$lot[1] : $fail;
    }

    public static function hooked($id = null, $stack = null, $fail = false) {
        if (isset($id)) {
            if (isset($stack)) {
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
                return isset(self::$lot_o[1][$id]) ? self::$lot_o[1][$id] : $fail;
            }
        }
        return !empty(self::$lot_o[1]) ? self::$lot_o[1] : $fail;
    }

    public static function fire($id = null, $lot = []) {
        if (isset($id)) {
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
                $routes = Anemon::eat(isset(self::$lot[1]) ? self::$lot[1] : [])->sort('ASC', 'stack', true)->vomit();
                foreach ($routes as $k => $v) {
                    // If matched with the URL path
                    if ($route = self::is($k)) {
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