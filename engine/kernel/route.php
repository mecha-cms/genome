<?php

final class Route extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate {

    private static $r;

    public $lot;
    public $match;

    public function __construct(string $path) {
        $r = explode('/', $path = trim($path, '/'));
        foreach ($r as &$v) {
            if (strpos($v, ':') === 0) {
                $v = '(?P<' . f2p(substr($v, 1)) . '>[^/]+)';
            } else {
                $v = str_replace("\\*", '(.+)', preg_quote($v));
            }
        }
        $r = '#^' . implode('/', $r) . '$#';
        $this->match = false;
        if (preg_match($r, trim($GLOBALS['URL']['path'], '/'), $m)) {
            array_shift($m); // Remove the first match
            $this->lot = e($m);
            $this->match = $path;
        }
    }

    public function __get(string $key) {
        if (method_exists($this, $key)) {
            if ((new \ReflectionMethod($this, $key))->isPublic()) {
                return $this->{$key}();
            }
        }
        return $this->lot[$key = p2f($key)] ?? null;
    }

    public function check(...$v) {
        return Guard::check(...$v);
    }

    public function content(string $v) {
        Hook::fire('set', [], $this);
        echo Hook::fire('content', [$v], $this);
        Hook::fire('get', [], $this);
        exit;
    }

    public function count() {
        return count($this->lot);
    }

    public function getIterator() {
        return new \ArrayIterator($this->lot);
    }

    public function header(...$v) {
        HTTP::header(...$v);
    }

    public function kick(...$v) {
        Guard::kick(...$v);
    }

    public function offsetExists($i) {
        return isset($this->lot[$i]);
    }

    public function offsetGet($i) {
        return $this->lot[$i] ?? null;
    }

    public function offsetSet($i, $value) {
        if (isset($i)) {
            $this->lot[$i] = $value;
        } else {
            $this->lot[] = $value;
        }
    }

    public function offsetUnset($i) {
        unset($this->lot[$i]);
    }

    public function refresh(...$v) {
        HTTP::refresh(...$v);
    }

    public function status(...$v) {
        HTTP::status(...$v);
    }

    public function trace($trace, string $separator = ' &#x00B7; ') {
        Config::set('trace', new Anemon((array) $trace, $separator));
    }

    public function type(...$v) {
        HTTP::type(...$v);
    }

    public static function fire(string $id, array $lot = []) {
        if (isset(self::$r[1][$id])) {
            // Loading hook(s)…
            if (isset(self::$r[2][$id])) {
                $fn = Anemon::eat(self::$r[2][$id])->sort([1, 'stack']);
                foreach ($fn as $v) {
                    fire($v['fn'], $lot, new static($id));
                }
            }
            fire(self::$r[1][$id]['fn'], $lot, new static($id));
        }
    }

    public static function get(string $id = null) {
        if (isset($id)) {
            return self::$r[1][$id] ?? null;
        }
        return self::$r[1] ?? [];
    }

    public static function is($id) {
        if (is_array($id)) {
            $out = [];
            foreach ($id as $v) {
                $out[$v] = self::is($v);
            }
            return $out;
        }
        $out = new static($id);
        return $out->match !== false ? $out : false;
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
                return self::$r[2][$id] ?? null;
            }
            if (!isset(self::$r[0][$id])) {
                self::$r[2][$id][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        }
    }

    public static function let($id = null) {
        if (is_array($id)) {
            foreach ($id as $v) {
                self::let($v);
            }
        } else if (isset($id)) {
            self::$r[0][$id] = self::$r[1][$id] ?? 1;
            unset(self::$r[1][$id]);
        } else {
            self::$r[1] = [];
        }
    }

    public static function set(...$lot) {
        // `Route::set('foo/bar', 404, function() {}, 10)`
        $id = array_shift($lot);
        $status = array_shift($lot) ?? 404;
        $fn = array_shift($lot);
        $stack = array_shift($lot) ?? 10;
        // `Route::set('foo/bar', function() {}, 10)`
        if (is_callable($status)) {
            $stack = $fn ?? 10;
            $fn = $status;
            $status = 404;
        }
        if (is_array($id)) {
            $i = 0;
            foreach ($id as $v) {
                self::set($v, $status, $fn, $stack + $i);
                $i += .1;
            }
        } else {
            if (!isset(self::$r[0][$id])) {
                self::$r[1][$id] = [
                    'fn' => $fn,
                    'stack' => (float) $stack,
                    'status' => $status
                ];
            }
        }
    }

    public static function start() {
        $data = e($GLOBALS['_' . ($m = strtoupper($_SERVER['REQUEST_METHOD']))] ?? []);
        $routes = Anemon::eat(self::$r[1] ?? [])->sort([1, 'stack'], true);
        foreach ($routes as $k => $v) {
            // If matched with the URL path, then …
            if (false !== ($route = self::is($k))) {
                // Loading hook(s)…
                if (isset(self::$r[2][$k])) {
                    $fn = Anemon::eat(self::$r[2][$k])->sort([1, 'stack']);
                    foreach ($fn as $f) {
                        fire($f['fn'], [$data, $m], $route);
                    }
                }
                // Passed!
                http_response_code($v['status']);
                fire($v['fn'], [$data, $m], $route);
                break;
            }
        }
    }

}