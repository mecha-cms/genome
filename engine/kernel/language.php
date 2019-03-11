<?php

final class Language extends Config {

    const config = [
        'id' => 'en-us'
    ];

    public static $config = self::config;

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        if ($lot) {
            $test = self::get($kin, ...$lot);
            // Asynchronous value with function closure
            if ($test instanceof \Closure) {
                return fn($test, $lot, $this, static::class);
            // Rich asynchronous value with class instance
            } else if ($fn = fn\is\instance($test)) {
                if (method_exists($fn, '__invoke')) {
                    return call_user_func([$fn, '__invoke'], ...$lot);
                }
            }
            // Else, static value
            return $test;
        }
        return self::get($kin, $kin);
    }

    public function __construct() {
        parent::__construct();
        $id = static::$config['id'];
        $f = constant(u($c = static::class)) . DS . $id . '.page';
        if (!is_file($f)) {
            return; // TODO
        }
        $content = Cache::hit($f, function($f): array {
            $f = file_get_contents($f);
            $fn = 'From::' . Page::apart($f, 'type');
            $content = Page::apart($f, 'content');
            return is_callable($fn) ? (array) call_user_func($fn, $content) : [];
        }) ?? [];
        self::$a[$c] = array_replace_recursive(self::$a[$c] ?? [], $content);
        self::$lot[$c] = array_replace_recursive(self::$lot[$c] ?? [], $content);
    }

    public function __get(string $key) {
        if (method_exists($this, $key)) {
            if ((new \ReflectionMethod($this, $key))->isPublic()) {
                return $this->{$key}();
            }
        }
        if (self::_($key)) {
            return $this->__call($key);
        }
        return self::get($key, $key);
    }

    public function __toString() {
        return To::YAML(self::get(null, true));
    }

    public static function get($key = null, $vars = [], $preserve_case = false) {
        $c = static::class;
        if (isset($key)) {
            $v = self::$lot[$c] ?? [];
            $v = Anemon::get($v, $key) ?? $key;
            $vars = array_replace([""], (array) $vars);
            if (is_string($v)) {
                if (!$preserve_case && strpos($v, '%') !== 0 && !ctype_upper($vars[0])) {
                    $vars[0] = l($vars[0] ?? "");
                }
                return candy($v, $vars);
            }
            return o($v);
        }
        return o(self::$lot[$c] ?? []);
    }

}