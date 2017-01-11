<?php

class Language extends Config {

    public static function ignite(...$lot) {
        $id = '__' . static::class;
        $language = parent::get('language');
        $cache = str_replace(ROOT, CACHE, LANGUAGE) . DS . $language . '.php';
        $x = File::open($cache)->import([0, []]);
        $i18n = File::exist([LANGUAGE . DS . $language . '.txt', LANGUAGE . DS . 'en-us.txt']);
        $t = File::T($i18n);
        if ($t > $x[0]) {
            $x = [$t, From::yaml($i18n)];
            File::export($x)->saveTo($cache);
        }
        return parent::set($id, $x[1])->get($id);
    }

    public static function set($key, $value = null) {
        $id = '__' . static::class . '.';
        if (!__is_anemon__($key)) {
            return parent::set($id . $key, $value);
        }
        foreach ($key as $k => $v) {
            $keys[$id . $k] = $v;
        }
        return parent::set(isset($keys) ? $keys : [], $value);
    }

    public static function get($key = null, $vars = [], $fail = null) {
        $vars = array_merge($vars, [""]);
        $fail = $fail ?: $key;
        $id = '__' . static::class;
        if (!isset($key)) {
            return parent::get($id, $fail);
        }
        $s = parent::get($id . '.' . $key, $fail);
        if (strpos($s, '%') !== 0 && u($vars[0]) !== $vars[0]) {
            $vars[0] = l($vars[0]);
        }
        return __replace__($s, $vars);
    }

    public static function __callStatic($kin, $lot) {
        return call_user_func_array([new static, $kin], $lot);
    }

    public function __construct($input = []) {
        if ($input) {
            self::set(From::yaml($input));
        }
    }

    public function __call($key, $lot) {
        return __replace__(parent::get('__' . static::class . '.' . $key, $key), array_merge($lot, [""]));
    }

    public function __set($key, $value = null) {
        return parent::set('__' . static::class . '.' . $key, $value);
    }

    public function __get($key) {
        return parent::get('__' . static::class . '.' . $key, $key);
    }

    public function __unset($key) {
        return parent::reset('__' . static::class . '.' . $key);
    }

    public function __toString() {
        return To::yaml(parent::get('__' . static::class));
    }

    public function __invoke($fail = []) {
        return parent::get('__' . static::class, o($fail));
    }

}