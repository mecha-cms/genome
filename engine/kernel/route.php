<?php

class Route extends Socket {

    public static $lot = [];
    public static $lot_o = [];

    // Pattern as regular expression
    protected function _x($s) {
        return str_replace([
            '\(',
            '\)',
            '\|',
            '\:any',
            '\:num',
            '\:all',
            '%s',
            '%i',
            '%*'
        ], [
            '(',
            ')',
            '|',
            '([^/]+)',
            '(\d+)',
            '(.*?)',
            '([^/]+)',
            '(\d+)',
            '(.*?)'
        ], x($s, '#'));
    }

    public static function add($id, $fn = null, $stack = null) {
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = URL::short($v, false);
            if (!isset(self::$lot[0][$v])) {
                self::$lot[1][$v] = [
                    'fn' => $fn,
                    'stack' => (float) (($stack[$k] ?? end($stack)) + $i)
                ];
                $i += .1;
            }
        }
        return true;
    }

    public static function added($id = null, $fail = false) {
        if ($id !== null) {
            return self::$lot[1][$id] ?? $fail;
        }
        return !empty(self::$lot[1]) ? self::$lot[1] : $fail;
    }

    // Alias for `Route::added()`
    public static function exist(...$lot) {
        return call_user_func_array('self::added', $lot);
    }

    public static function get(...$lot) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return call_user_func_array('self::add', $lot);
        }
        return false;
    }

    public static function post(...$lot) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return call_user_func_array('self::add', $lot);
        }
        return false;
    }

    public static function remove($id) {
        foreach ((array) $id as $v) {
            $v = URL::short($v, false);
            self::$lot[0][$v] = self::$lot[1][$v] ?? 1;
            unset($this->lot[1][$v]);
        }
        return true;
    }

    public static function removed($id = null, $fail = false) {
        if ($id === null) {
            return !empty(self::$lot[0]) ? self::$lot[0] : $fail;
        }
        return self::$lot[0][$id] ?? $fail;
    }

    public static function hook($id, $fn = null, $stack = null) {
        $id = URL::short($id, false);
        $stack = $stack ?? 10;
        if (!isset(self::$lot_o[1][$id])) {
            self::$lot_o[1][$id] = [];
        }
        self::$lot_o[1][$id][] = [
            'fn' => $fn,
            'stack' => (float) $stack
        ];
        return true;
    }

    public static function hooked($id = null, $stack = null, $fail = false) {
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

    public static function is($id, $fail = false) {
        $id = URL::short($id, false);
        $path = Config::url('path');
        if (strpos($id, ':') === false && strpos($id, '%') === false) {
            return $path === $id ? [
                'id' => $id,
                'path' => $path,
                'lot' => []
            ] : $fail;
        }
        if (preg_match('#^' . self::_x($id) . '$#', $path, $m)) {
            array_shift($m);
            return [
                'id' => $id,
                'path' => $path,
                'lot' => e($m)
            ];
        }
        return $fail;
    }

    public static function fire($id = null, $lot = []) {
        if ($id !== null) {
            $id = URL::short($id, false);
            if (isset(self::$lot[1][$id])) {
                call_user_func_array(self::$lot[1][$id]['fn'], $lot);
                return true;
            }
            return false;
        } else {
            $id = Config::url('path');
            if (isset(self::$lot[1][$id])) {
                // Loading cargo(s) ...
                if (isset(self::$lot_o[1][$id])) {
                    $fn = Anemon::eat(self::$lot_o[1][$id])->sort('ASC', 'stack')->vomit();
                    foreach ($fn as $v) {
                        call_user_func_array($v['fn'], $lot);
                    }
                }
                // Passed!
                return call_user_func_array(self::$lot[1][$id]['fn'], $lot);
            } else {
                $routes = Anemon::eat(self::$lot[1])->sort('ASC', 'stack', true)->vomit();
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
                        return call_user_func_array($v['fn'], $route['lot']);
                    }
                }
            }
        }
    }

}