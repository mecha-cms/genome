<?php

class Language extends Config {

    protected static function set_static($a, $b = null) {
        if (!__is_anemon__($a)) {
            parent::set_static('__i18n.' . $a, $b);
        } else {
            foreach ($a as $k => $v) {
                $aa['__i18n.' . $k] = $v;
            }
            parent::set_static($aa ?? [], $b);
        }
    }

    protected static function get_static($k = null, $a = [], $fail = null) {
        $a = array_merge($a, [""]);
        $fail = $fail ?? $k;
        if ($k === null) return parent::get_static('__i18n', $fail);
        $s = parent::get_static('__i18n.' . $k, $fail);
        if (strpos($s, '%') !== 0 && u($a[0]) !== $a[0]) {
            $a[0] = l($a[0]);
        }
        return vsprintf($s, $a);
    }

    public static function __callStatic($kin, $lot) {
        $kin = '__i18n.' . $kin;
        return parent::__callStatic($kin, $lot);
    }

    public function __call($key, $lot) {
        return vsprintf(parent::get_static('__i18n.' . $key, $key), array_merge($lot, [""]));
    }

    public function __get($key) {
        return parent::get_static('__i18n.' . $key, $key);
    }

    public function __set($key, $value = null) {
        parent::set_static('__i18n.' . $key, $value);
    }

    public function __toString() {
        return json_encode(parent::get_static('__i18n'));
    }

    public function __invoke($fail = []) {
        return parent::get_static('__i18n', o($fail));
    }

}