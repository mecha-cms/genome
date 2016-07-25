<?php

class Route extends __ {

    public static $routes = [];
    public static $routes_x = [];
    public static $routes_over = [];

    // Pattern as regular expression
    protected static function _x($string) {
        return str_replace(
            array('\(', '\)', '\|', '\:any', '\:num', '\:all', '#'),
            array('(', ')', '|', '[^/]+', '\d+', '.*?', '\#'),
        preg_quote($string, '/'));
    }

    // Remove the root URL
    protected static function _path($pattern) {
        return trim(str_replace(Config::get('url') . '/', "", $pattern), '/');
    }

    /**
     * ===========================================================================
     *  GLOBAL ROUTE PATTERN MATCH
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::accept('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     *    Route::accept('foo/(:num)', function($o = 1) {
     *        ...
     *    });
     *
     * ---------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type     | Description
     *  --------- | -------- | ---------------------------------------------------
     *  $pattern  | string   | URL pattern to match
     *  $fn       | function | Route function to be executed on URL pattern match
     *  $stack    | float    | Route function priority
     *  --------- | -------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function accept($pattern, $fn = null, $stack = null) {
        $url = Config::get('url');
        $stack = !is_null($stack) ? $stack : 10;
        if ( !is_array($pattern)) {
            if ( !isset(self::$routes_x[$pattern])) {
                self::$routes[$pattern] = array(
                    'fn' => $fn,
                    'stack' => (float) $stack
                );
            }
        } else {
            $i = 0;
            foreach ($pattern as $p) {
                if ( !isset(self::$routes_x[$p])) {
                    self::$routes[$p] = array(
                        'fn' => $fn,
                        'stack' => (float) $stack + $i
                    );
                    $i += .1;
                }
            }
        }
    }

    /**
     * ===========================================================================
     *  CHECK FOR THE ACCEPTED ROUTE
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    $test = Route::accepted('foo/bar');
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function accepted($pattern = null, $fail = false) {
        if ( !is_null($pattern)) {
            return isset(self::$routes[$pattern]) ? self::$routes[$pattern] : $fail;
        }
        return !empty(self::$routes) ? self::$routes : $fail;
    }

    // alias for `Route::accepted()`
    public static function exist($pattern = null, $fail = false) {
        return self::accepted($pattern, $fail);
    }

    /**
     * ===========================================================================
     *  GET REQUEST ONLY
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::get('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function get($pattern, $fn, $stack = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            self::accept($pattern, $fn, $stack);
        }
    }

    /**
     * ===========================================================================
     *  POST REQUEST ONLY
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::post('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function post($pattern, $fn, $stack = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::accept($pattern, $fn, $stack);
        }
    }

    /**
     * ===========================================================================
     *  REJECT SPECIFIC ROUTE PATTERN
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::reject('foo/bar');
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function reject($pattern) {
        if ( !is_array($pattern)) {
            $pattern = self::_path($pattern);
            self::$routes_x[$pattern] = isset(self::$routes[$pattern]) ? self::$routes[$pattern] : 1;
            unset(self::$routes[$pattern]);
        } else {
            foreach ($pattern as $p) {
                $p = self::_path($p);
                self::$routes_x[$p] = isset(self::$routes[$p]) ? self::$routes[$p] : 1;
                unset(self::$routes[$p]);
            }
        }
    }

    /**
     * ===========================================================================
     *  CHECK FOR THE REJECTED ROUTE(S)
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    $test = Route::rejected('foo/bar');
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function rejected($pattern = null, $fail = false) {
        if ( !is_null($pattern)) {
            return isset(self::$routes_x[$pattern]) ? self::$routes_x[$pattern] : $fail;
        }
        return !empty(self::$routes_x) ? self::$routes_x : $fail;
    }

    /**
     * ===========================================================================
     *  DO SOMETHING BEFORE THE `$pattern` ACTION
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::over('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function over($pattern, $fn = null, $stack = null) {
        $pattern = self::_path($pattern);
        $stack = !is_null($stack) ? $stack : 10;
        if ( !isset(self::$routes_over[$pattern])) {
            self::$routes_over[$pattern] = [];
        }
        self::$routes_over[$pattern][] = array(
            'fn' => $fn,
            'stack' => (float) $stack
        );
    }

    /**
     * ===========================================================================
     *  CHECK IF `$pattern` ALREADY OVERED
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    if (Route::overed('foo/bar')) { ... }
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function overed($pattern = null, $stack = null, $fail = false) {
        if ( !is_null($pattern)) {
            if ( !is_null($stack)) {
                $routes = [];
                foreach (self::$routes_over[$pattern] as $route) {
                    if (
                        is_numeric($stack) && $route['stack'] === (float) $stack ||
                        $route['fn'] === $stack
                    ) {
                        $routes[] = $route;
                    }
                }
                return !empty($routes) ? $routes : $fail;
            } else {
                return isset(self::$routes_over[$pattern]) ? self::$routes_over[$pattern] : $fail;
            }
        }
        return !empty(self::$routes_over) ? self::$routes_over : $fail;
    }

    /**
     * ===========================================================================
     *  CHECK FOR ROUTE PATTERN MATCH
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    if (Route::is('foo/bar')) { ... }
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function is($pattern, $fail = false) {
        $pattern = self::_path($pattern);
        $path = Config::get('url_path');
        if (strpos($pattern, '(') === false) {
            return $path === $pattern ? array(
                'pattern' => $pattern,
                'path' => $path,
                'lot' => []
            ) : $fail;
        }
        if (preg_match('#^' . self::_x($pattern) . '$#', $path, $matches)) {
            array_shift($matches);
            return array(
                'pattern' => $pattern,
                'path' => $path,
                'lot' => Converter::strEval($matches)
            );
        }
        return $fail;
    }

    /**
     * ===========================================================================
     *  EXECUTE THE ADDED ROUTE(S)
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::execute();
     *
     * ---------------------------------------------------------------------------
     *
     *    Route::execute('foo/(:num)', array(4)); // Re-execute `foo/(:num)`
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function execute($pattern = null, $arguments = []) {
        if ( !is_null($pattern)) {
            $pattern = self::_path($pattern);
            if (isset(self::$routes[$pattern])) {
                call_user_func_array(self::$routes[$pattern]['fn'], $arguments);
            }
        } else {
            $pattern = Config::get('url_path');
            if (isset(self::$routes[$pattern])) {
                // Loading cargo(s) ...
                if (isset(self::$routes_over[$pattern])) {
                    $fn = Mecha::eat(self::$routes_over[$pattern])->order('ASC', 'stack')->vomit();
                    foreach ($fn as $v) {
                        call_user_func($v['fn']);
                    }
                }
                // Passed!
                return call_user_func(self::$routes[$pattern]['fn']);
            } else {
                $routes = Mecha::eat(self::$routes)->order('ASC', 'stack', true)->vomit();
                foreach ($routes as $pattern => $cargo) {
                    // If matched with the URL path
                    if ($route = self::is($pattern)) {
                        // Loading cargo(s) ...
                        if (isset(self::$routes_over[$pattern])) {
                            $fn = Mecha::eat(self::$routes_over[$pattern])->order('ASC', 'stack')->vomit();
                            foreach ($fn as $v) {
                                call_user_func_array($v['fn'], $route['lot']);
                            }
                        }
                        // Passed!
                        return call_user_func_array($cargo['fn'], $route['lot']);
                    }
                }
            }
        }
    }

}