<?php

class Language extends Config {

    public static function ignite(...$lot) {
        $language = Config::get('language');
        $f = LANGUAGE . DS . $language . '.page';
        if (Cache::expire($f)) {
            $i18n = new Page($f, [], ['*', 'language']);
            $fn = 'From::' . p($i18n->type);
            $c = $i18n->content;
            $content = is_callable($fn) ? call_user_func($fn, $c) : (array) $c;
            Cache::set($f, $content);
        } else {
            $content = Cache::get($f);
        }
        return self::set($content)->get();
    }

    public static function get($key = null, $vars = [], $preserve_case = false) {
        $c = static::class;
        if (!isset($key)) {
            return !empty(self::$bucket[$c]) ? o(self::$bucket[$c]) : [];
        }
        $v = isset(self::$bucket[$c]) ? (array) self::$bucket[$c] : [];
        $v = Anemon::get($v, $key, $key);
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

    public function __toString() {
        return To::YAML(self::get());
    }

}