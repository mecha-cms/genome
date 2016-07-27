<?php

class Route extends DNA {

    public $lot = [];
    public $lot_o = [];

    // Pattern as regular expression
    protected function _x($s) {
        return str_replace(
            ['\(', '\)', '\|', '%s', '%i', '%\*', '#'],
            ['(', ')', '|', '([^/]+)', '(\d+)', '(.*?)', '\#'],
        preg_quote($s, '/'));
    }

    // Remove the root URL
    protected function _path($pattern) {
        return trim(str_replace(URL::url() . '/', "", $pattern), '/');
    }

    public function accept($pattern, $fn = null, $stack = null) {
        $url = URL::url();
        $stack = $stack ?? 10;
        if (!is_array($pattern)) {
            if (!isset($this->lot[0][$pattern])) {
                $this->lot[1][$pattern] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        } else {
            $i = 0;
            $stack = (array) $stack;
            foreach ($pattern as $k => $v) {
                if (!isset($this->lot[0][$v])) {
                    $this->lot[1][$v] = [
                        'fn' => $fn,
                        'stack' => $stack[$k] ?? (float) end($stack) + $i
                    ];
                    $i += .1;
                }
            }
        }
    }

    public function accepted($pattern = null, $fail = false) {
        if ($pattern !== null) {
            return $this->lot[1][$pattern] ?? $fail;
        }
        return !empty($this->lot[1]) ? $this->lot[1] : $fail;
    }

    // alias for `Route::accepted()`
    public function exist() {
        return call_user_func_array([$this, 'accepted'], func_get_args());
    }

    public function get() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            call_user_func_array([$this, 'accept'], func_get_args());
        }
    }

    public function post() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            call_user_func_array([$this, 'accept'], func_get_args());
        }
    }

    public function reject($pattern) {
        if (!is_array($pattern)) {
            $pattern = $this->_path($pattern);
            $this->lot[0][$pattern] = $this->lot[1][$pattern] ?? 1;
            unset($this->lot[1][$pattern]);
        } else {
            foreach ($pattern as $v) {
                $v = $this->_path($v);
                $this->lot[0][$v] = $this->lot[1][$v] ?? 1;
                unset($this->lot[1][$v]);
            }
        }
    }

    public function rejected($pattern = null, $fail = false) {
        if ($pattern !== null) {
            return $this->lot[0][$pattern] ?? $fail;
        }
        return !empty($this->lot[0]) ? $this->lot[0] : $fail;
    }

    public function over($pattern, $fn = null, $stack = null) {
        $pattern = $this->_path($pattern);
        $stack = !is_null($stack) ? $stack : 10;
        if (!isset($this->lot_o[1][$pattern])) {
            $this->lot_o[1][$pattern] = [];
        }
        $this->lot_o[1][$pattern][] = [
            'fn' => $fn,
            'stack' => (float) $stack
        ];
    }

    public function overed($pattern = null, $stack = null, $fail = false) {
        if ($pattern !== null) {
            if ($stack !== null) {
                $routes = [];
                foreach ($this->lot_o[1][$pattern] as $route) {
                    if (
                        is_numeric($stack) && $route['stack'] === (float) $stack ||
                        $route['fn'] === $stack
                    ) {
                        $routes[] = $route;
                    }
                }
                return !empty($routes) ? $routes : $fail;
            } else {
                return $this->lot_o[1][$pattern] ?? $fail;
            }
        }
        return !empty($this->lot_o[1]) ? $this->lot_o[1] : $fail;
    }

    public function is($pattern, $fail = false) {
        $pattern = $this->_path($pattern);
        $path = URL::path();
        if (strpos($pattern, '(') === false) {
            return $path === $pattern ? [
                'pattern' => $pattern,
                'path' => $path,
                'lot' => []
            ] : $fail;
        }
        if (preg_match('#^' . $this->_x($pattern) . '$#', $path, $m)) {
            array_shift($m);
            return [
                'pattern' => $pattern,
                'path' => $path,
                'lot' => e($m)
            ];
        }
        return $fail;
    }

    public function execute($pattern = null, $lot = []) {
        if ($pattern !== null) {
            $pattern = $this->_path($pattern);
            if (isset($this->lot[1][$pattern])) {
                call_user_func_array($this->lot[1][$pattern]['fn'], $lot);
            }
        } else {
            $pattern = URL::path();
            if (isset($this->lot[1][$pattern])) {
                // Loading cargo(s) ...
                if (isset($this->lot_o[1][$pattern])) {
                    $fn = Anemon::eat($this->lot_o[1][$pattern])->sort('ASC', 'stack')->vomit();
                    foreach ($fn as $v) {
                        call_user_func_array($v['fn']);
                    }
                }
                // Passed!
                return call_user_func($this->lot[1][$pattern]['fn']);
            } else {
                $routes = Anemon::eat($this->lot[1])->sort('ASC', 'stack', true)->vomit();
                foreach ($routes as $k => $v) {
                    // If matched with the URL path
                    if ($route = $this->is($k)) {
                        // Loading cargo(s) ...
                        if (isset($this->lot_o[1][$k])) {
                            $fn = Anemon::eat($this->lot_o[1][$k])->sort('ASC', 'stack')->vomit();
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