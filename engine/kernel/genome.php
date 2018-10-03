<?php

abstract class Genome {

    // Instance(s)…
    public static $__instance__ = [];

    // Method(s)…
    public static $_ = [];

    // Set, get, reset…
    public static function _(...$lot) {
        $c = static::class;
        if (count($lot) === 0) {
            return isset(self::$_[$c]) ? self::$_[$c] : [];
        } else if (count($lot) === 1) {
            return isset(self::$_[$c][$lot[0]]) ? self::$_[$c][$lot[0]] : false;
        } else if ($lot[1] === null) {
            unset(self::$_[$c][$lot[0]]);
            return true;
        }
        self::$_[$c][$lot[0]] = (array) $lot[1];
        return true;
    }

    // Count the instance with `count(Genome::$__instance__)`
    public function __construct() {
        self::$__instance__[] = $this;
    }

    // Call the added method with `$genome->foo()`
    public function __call($kin, $lot = []) {
        $c = static::class;
        if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
            $a = self::$_[$c][$kin];
            if (is_callable($a[0])) {
                // Alter default function argument(s)
                if (isset($a[1])) {
                    $lot = array_replace_recursive((array) $a[1], $lot);
                }
                // Limit function argument(s)
                if (isset($a[2])) {
                    $lot = array_slice($lot, 0, $a[2]);
                }
                return call_user_func(\Closure::bind($a[0], $this), ...$lot);
            }
            return $a[0];
        } else if (method_exists($this, '_' . $kin) && !(new ReflectionMethod($this, '_' . $kin))->isPublic()) {
            return $this->{'_' . $kin}(...$lot);
        } else if (defined('DEBUG') && DEBUG) {
            echo '<p>Method <code>$' . __c2f__($c, '_', '/') . '-&gt;' . $kin . '()</code> does not exist.</p>';
        }
        return false;
    }

    // Call the added method with `Genome::foo()`
    public static function __callStatic($kin, $lot = []) {
        $c = static::class;
        if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
            $a = self::$_[$c][$kin];
            if (is_callable($a[0])) {
                // Alter default function argument(s)
                if (isset($a[1])) {
                    $lot = array_replace_recursive((array) $a[1], $lot);
                }
                // Limit function argument(s)
                if (isset($a[2])) {
                    $lot = array_slice($lot, 0, $a[2]);
                }
                return call_user_func($a[0], ...$lot);
            }
            return $a[0];
        } else if (method_exists($that = new static, '_' . $kin) && !(new ReflectionMethod($that, '_' . $kin))->isPublic()) {
            return $that->{'_' . $kin}(...$lot);
        } else if (defined('DEBUG') && DEBUG) {
            echo '<p>Method <code>' . $c . '::' . $kin . '()</code> does not exist.</p>';
        }
        return false;
    }

}