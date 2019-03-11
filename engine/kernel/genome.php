<?php

abstract class Genome {

    // Method(s)…
    public static $_ = [];
    // Instance(s)…
    public static $__instance__ = [];

    // Call the added method with `$genome->foo()`
    public function __call(string $kin, array $lot = []) {
        $c = static::class;
        $m = '_' . $kin . '_';
        $this->_call = T_OBJECT_OPERATOR;
        if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
            $a = self::$_[$c][$kin];
            if (is_callable($a[0])) {
                // Alter default function argument(s)
                if (isset($a[1])) {
                    $lot = array_replace((array) $a[1], $lot);
                }
                // Limit function argument(s)
                if (isset($a[2])) {
                    $lot = array_slice($lot, 0, $a[2]);
                }
                return fn($a[0], $lot, $this/*, $c */);
            }
            return $a[0];
        } else if (method_exists($this, $m) && (new \ReflectionMethod($this, $m))->isProtected()) {
            return $this->{$m}(...$lot);
        } else if (defined('DEBUG') && DEBUG) {
            throw new \BadMethodCallException('Method $' . c2f($c, '_', '/') . '->' . $kin . '() does not exist.');
        }
    }

    // Count the instance with `count(Genome::$__instance__)`
    public function __construct() {
        self::$__instance__[] = $this;
    }

    public function __get(string $kin) {
        if (method_exists($this, $kin)) {
            if ((new \ReflectionMethod($this, $kin))->isPublic()) {
                return $this->{$kin}();
            }
        }
        return $this->__call($kin);
    }

    // Set, get, reset…
    public static function _(...$lot) {
        $c = static::class;
        if (count($lot) === 0) {
            return self::$_[$c] ?? [];
        } else if (count($lot) === 1) {
            return self::$_[$c][$lot[0]] ?? null;
        } else if ($lot[1] === null) {
            unset(self::$_[$c][$lot[0]]);
        } else {
            self::$_[$c][$lot[0]] = (array) $lot[1];
        }
    }

    // Call the added method with `Genome::foo()`
    public static function __callStatic(string $kin, array $lot = []) {
        $c = static::class;
        $m = '_' . $kin . '_';
        $that = new static;
        $that->_call = T_DOUBLE_COLON;
        if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
            $a = self::$_[$c][$kin];
            if (is_callable($a[0])) {
                // Alter default function argument(s)
                if (isset($a[1])) {
                    $lot = array_replace((array) $a[1], $lot);
                }
                // Limit function argument(s)
                if (isset($a[2])) {
                    $lot = array_slice($lot, 0, $a[2]);
                }
                return fn($a[0], $lot, $that/*, $c */);
            }
            return $a[0];
        } else if (method_exists($that, $m) && (new \ReflectionMethod($that, $m))->isProtected()) {
            return $that->{$m}(...$lot);
        } else if (defined('DEBUG') && DEBUG) {
            throw new \BadMethodCallException('Method ' . $c . '::' . $kin . '() does not exist.');
        }
    }

}