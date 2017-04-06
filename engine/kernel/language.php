<?php

class Language extends Genome {

    public static function ignite(...$lot) {
        $id = '__' . static::class;
        $language = Config::get('language');
        $cache = str_replace(ROOT, CACHE, LANGUAGE) . DS . $language . '.php';
        $x = File::open($cache)->import([0, []]);
        $i18n = File::exist([LANGUAGE . DS . $language . '.page', LANGUAGE . DS . 'en-us.page'], null);
        $t = File::T($i18n, -1);
        $i18n = new Page($i18n, [], 'language');
        if ($x[0] === 0 || $t > $x[0]) {
            $fn = 'From::' . l($i18n->type);
            $x = [$t, is_callable($fn) ? call_user_func($fn, $i18n->content) : $i18n->content];
            File::export($x)->saveTo($cache);
        }
        return Config::set($id, $x[1])->get($id);
    }

    public static function set($key, $value = null) {
        $id = '__' . static::class . '.';
        if (!__is_anemon__($key)) {
            return Config::set($id . $key, $value);
        }
        foreach ($key as $k => $v) {
            $keys[$id . $k] = $v;
        }
        return Config::set(isset($keys) ? $keys : [], $value);
    }

    public static function get($key = null, $vars = [], $preserve_case = false) {
        $vars = array_merge(s((array) $vars), [""]);
        $fail = $key;
        $id = '__' . static::class;
        if (!isset($key)) {
            return Config::get($id, $fail);
        }
        $s = Config::get($id . '.' . $key, $fail);
        if (is_string($s)) {
            if (!$preserve_case && strpos($s, '%') !== 0 && u($vars[0]) !== $vars[0]) {
                $vars[0] = l($vars[0]);
            }
            return __replace__($s, $vars);
        }
        return $s;
    }

    public static function __callStatic($kin, $lot) {
        return call_user_func_array([new static, $kin], $lot);
    }

    public function __construct($input = []) {
        if ($input) {
            self::set(From::yaml($input));
        }
        parent::__construct();
    }

    public function __call($key, $lot) {
        return self::get($key, array_shift($lot), array_shift($lot) ?: false);
    }

    public function __set($key, $value = null) {
        return self::set($key, $value);
    }

    public function __get($key) {
        return Config::get('__' . static::class . '.' . $key, $key);
    }

    public function __unset($key) {
        return Config::reset('__' . static::class . '.' . $key);
    }

    public function __toString() {
        return To::yaml(Config::get('__' . static::class));
    }

    public function __invoke($fail = []) {
        return Config::get('__' . static::class, o($fail));
    }

}