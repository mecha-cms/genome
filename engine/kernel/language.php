<?php

class Language extends Config {

    public static function set($a, $b = null) {
        if (!__is_anemon__($a)) {
            return parent::set('__i18n.' . $a, $b);
        }
        foreach ($a as $k => $v) {
            $aa['__i18n.' . $k] = $v;
        }
        return parent::set(isset($aa) ? $aa : [], $b);
    }

    public static function get($k = null, $a = [], $fail = null) {
        $a = array_merge($a, [""]);
        $fail = $fail ?: $k;
        if (!isset($k)) return parent::get('__i18n', $fail);
        $s = parent::get('__i18n.' . $k, $fail);
        if (strpos($s, '%') !== 0 && u($a[0]) !== $a[0]) {
            $a[0] = l($a[0]);
        }
        return vsprintf($s, $a);
    }

    public static function __callStatic($kin, $lot) {
        return call_user_func_array([new static, $kin], $lot);
    }

    public function __call($key, $lot) {
        return vsprintf(parent::get('__i18n.' . $key, $key), array_merge($lot, [""]));
    }

    public function __get($key) {
        return parent::get('__i18n.' . $key, $key);
    }

    public function __set($key, $value = null) {
        return parent::set('__i18n.' . $key, $value);
    }

    public function __toString() {
        return To::yaml(parent::get('__i18n'));
    }

    public function __invoke($fail = []) {
        return parent::get('__i18n', o($fail));
    }

}