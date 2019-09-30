<?php

final class Route extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate {

    private function __construct(string $path) {
        $r = explode('/', $path = trim($path, '/'));
        foreach ($r as &$v) {
            if (strpos($v, ':') === 0 && strlen($v) > 1) {
                $v = '(?P<' . preg_quote(substr($v, 1)) . '>[^/]+)';
            } else if ($v === '*') {
                $v = '(.+)';
            } else {
                $v = preg_quote($v);
            }
            $v = strtr($v, ["\\\\:" => ':']);
        }
        $r = '#^' . implode('/', $r) . '$#';
        $this->match = false;
        if (preg_match($r, trim($GLOBALS['url']->path, '/'), $m)) {
            array_shift($m); // Remove the first match
            $this->lot = e($m);
            $this->match = $path;
        }
    }

    private static $r;

    public $lot;
    public $match;

    public function __get(string $key) {
        return $this->lot[p2f($key)] ?? null;
    }

    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __set(string $key, $value) {
        $this->lot[p2f($key)] = $value;
    }

    public function __unset(string $key) {
        unset($this->lot[p2f($key)]);
    }

    public function count() {
        return count($this->lot);
    }

    public function fire(string $id, array $value = [], array $lot = []) {
        if ($v = self::get($id)) {
            $route = new static($id);
            foreach ($value as $k => $v) {
                $route->lot[p2f($k)] = $v;
            }
            fire($v['fn'], [$lot, ucfirst(strtolower($_SERVER['REQUEST_METHOD']))], $route);
        }
    }

    public function getIterator() {
        return new \ArrayIterator($this->lot);
    }

    public function header(...$v) {
        return count($v) > 1 || is_array($v[0]) ? Header::set(...$v) : Header::get(...$v);
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

    public function status(...$v) {
        return Header::status(...$v);
    }

    public function type(...$v) {
        return Header::type(...$v);
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

    public static function over($id, callable $fn = null, float $stack = 10) {
        if (is_array($id)) {
            if (!isset($fn)) {
                $out = [];
                foreach ($id as $v) {
                    $out[$v] = self::over($v);
                }
                return $out;
            }
            $i = 0;
            foreach ($id as $v) {
                self::over($v, $fn, $stack + $i);
                $i += .1;
            }
        } else {
            if (!isset($fn)) {
                return self::$r[2][$id] ?? null;
            }
            if (empty(self::$r[0][$id])) {
                self::$r[2][$id][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        }
    }

    public static function set(...$lot) {
        // `Route::set('foo/bar', 404, function() {}, 10)`
        $id = $lot[0] ?? null;
        $status = $lot[1] ?? 200;
        $fn = $lot[2] ?? null;
        $stack = $lot[3] ?? 10;
        // `Route::set('foo/bar', function() {}, 10)`
        if (is_callable($status)) {
            $stack = $fn ?? 10;
            $fn = $status;
            $status = 200;
        }
        if (is_array($id)) {
            $i = 0;
            foreach ($id as $v) {
                self::set($v, $status, $fn, $stack + $i);
                $i += .1;
            }
        } else {
            if (empty(self::$r[0][$id])) {
                self::$r[1][$id] = [
                    'fn' => $fn,
                    'stack' => (float) $stack,
                    'status' => $status
                ];
            }
        }
    }

    public static function start() {
        $routes = (new Anemon(self::$r[1] ?? []))->sort([1, 'stack'], true);
        $data = e($GLOBALS['_' . ($t = $_SERVER['REQUEST_METHOD'])] ?? []);
        $t = ucfirst(strtolower($t)); // Request type
        foreach ($routes as $k => $v) {
            // If matched with the URL path, then …
            if (false !== ($route = self::is($k))) {
                // Loading hook(s)…
                if (isset(self::$r[2][$k])) {
                    $fn = (new Anemon(self::$r[2][$k]))->sort([1, 'stack']);
                    foreach ($fn as $f) {
                        fire($f['fn'], [$data, $t], $route);
                    }
                }
                // Passed!
                http_response_code($v['status']);
                fire($v['fn'], [$data, $t], $route);
                break;
            }
        }
    }

}