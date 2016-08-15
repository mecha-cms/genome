<?php

class Language extends Config {

    public static function set($a, $b = null) {
        if (!__is_anemon__($a)) {
            parent::set('__i18n.' . $a, $b);
        } else {
            foreach ($a as $k => $v) {
                $aa['__i18n.' . $k] = $v;
            }
            parent::set($aa ?? [], $b);
        }
    }

    public static function get($k = null, $a = [], $fail = null) {
        $a = $a + [""];
        $fail = $fail ?? $k;
        if ($k === null) return parent::get('__i18n', $fail);
        $s = parent::get('__i18n.' . $k, $fail);
        if (strpos($s, '%') !== 0 && u($a[0]) !== $a[0]) {
            $a[0] = l($a[0]);
        }
        return vsprintf($s, $a);
    }

    public static function __callStatic($kin, $lot) {
        $kin = '__i18n.' . $kin;
        return parent::__callStatic($kin, $lot);
    }

}