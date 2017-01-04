<?php

class Language extends Config {

    public static function set($key, $value = null) {
        if (!__is_anemon__($key)) {
            return parent::set('__i18n.' . $key, $value);
        }
        foreach ($key as $k => $v) {
            $keys['__i18n.' . $k] = $v;
        }
        return parent::set(isset($keys) ? $keys : [], $value);
    }

    public static function get($key = null, $vars = [], $fail = null) {
        $vars = array_merge($vars, [""]);
        $fail = $fail ?: $key;
        if (!isset($key)) return parent::get('__i18n', $fail);
        $s = parent::get('__i18n.' . $key, $fail);
        if (strpos($s, '%') !== 0 && u($vars[0]) !== $vars[0]) {
            $vars[0] = l($vars[0]);
        }
        return __replace__($s, $vars);
    }

    public static function __callStatic($kin, $lot) {
        return call_user_func_array([new static, $kin], $lot);
    }

    public function __call($key, $lot) {
        return __replace__(parent::get('__i18n.' . $key, $key), array_merge($lot, [""]));
    }

    public function __set($key, $value = null) {
        return parent::set('__i18n.' . $key, $value);
    }

    public function __get($key) {
        return parent::get('__i18n.' . $key, $key);
    }

    public function __unset($key) {
        return parent::reset('__i18n.' . $key);
    }

    public function __toString() {
        return To::yaml(parent::get('__i18n'));
    }

    public function __invoke($fail = []) {
        return parent::get('__i18n', o($fail));
    }

}