<?php

class Language extends Config {

    public static function ignite(...$lot) {
        $language = Config::get('language');
        $f = LANGUAGE . DS . $language . '.page';
        $content = Cache::expire($f) ? Cache::set($f, function($f) {
            $i18n = new Page($f, [], ['*', 'language']);
            $fn = 'From::' . $i18n->type;
            $c = $i18n->content;
            return is_callable($fn) ? call_user_func($fn, $c) : (array) $c;
        }) : Cache::get($f);
        return self::set($content)->get();
    }

    public static function get($key = null, $vars = [], $preserve_case = false) {
        $c = static::class;
        if (!isset($key)) {
            return !empty(self::$bucket[$c]) ? o(self::$bucket[$c]) : [];
        }
        $v = isset(self::$bucket[$c]) ? (array) self::$bucket[$c] : [];
        $v = Anemon::get($v, $key, $key);
        $vars = array_replace([""], (array) $vars);
        if (is_string($v)) {
            if (!$preserve_case && strpos($v, '%') !== 0 && u($vars[0]) !== $vars[0]) {
                $vars[0] = l($vars[0]);
            }
            return __replace__($v, $vars);
        }
        return o($v);
    }

    public function __construct($input = []) {
        parent::__construct(is_array($input) ? $input : From::YAML($input));
    }

    public function __call($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $fail = $alt = false;
        if (count($lot)) {
            $test = self::get($kin);
            // Asynchronous value with function closure
            if ($test instanceof \Closure) {
                return call_user_func($test, ...$lot);
            // Rich asynchronous value with class instance
            } else if ($fn = __is_instance__($test)) {
                if (method_exists($fn, '__invoke')) {
                    return call_user_func([$fn, '__invoke'], ...$lot);
                }
            }
            // Else, static value
            return self::get($kin, ...$lot);
        }
    }

    public function __toString() {
        return To::YAML(self::get());
    }

}