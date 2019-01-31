<?php

class Language extends Config {

    const config = [
        'id' => 'en-us'
    ];

    public static $config = self::config;

    public static function get($key = null, $vars = [], $preserve_case = false) {
        $c = static::class;
        if (!isset($key)) {
            return !empty(self::$lot[$c]) ? o(self::$lot[$c]) : [];
        }
        $v = (array) (self::$lot[$c] ?? []);
        $v = Anemon::get($v, $key, $key);
        $vars = extend([""], (array) $vars, false);
        if (is_string($v)) {
            if (!$preserve_case && strpos($v, '%') !== 0 && !ctype_upper($vars[0])) {
                $vars[0] = l($vars[0] ?? "");
            }
            return candy($v, $vars);
        }
        return o($v);
    }

    public function __construct() {
        parent::__construct();
        $id = static::$config['id'];
        $f = constant(u($c = static::class)) . DS . $id . '.page';
        if (!file_exists($f)) {
            return; // TODO
        }
        $content = Cache::of($f, function() use($f) {
            $fn = 'From::' . Page::apart($f, 'type', "");
            $content = Page::apart($f, 'content', "");
            return is_callable($fn) ? call_user_func($fn, $content) : [];
        }, filemtime($f), []);
        self::$lot[$c] = extend(self::$lot[$c] ?? [], $content);
        self::$a[$c] = extend(self::$a[$c] ?? [], $content);
    }

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $fail = $alt = false;
        if (count($lot)) {
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

    public function __get(string $key) {
        return self::get($key, $key);
    }

    public function __toString() {
        return To::YAML(self::get());
    }

}