<?php namespace Gene;

class I18N extends \Socket {

    public function __call($key, $lot) {
        return vsprintf(\Config::get('__i18n.' . $key, $key), $lot + [""]);
    }

    public function __set($key, $value = null) {
        \Config::set('__i18n.' . $key, $value);
    }

    public function __get($key) {
        return \Config::get('__i18n.' . $key, $key);
    }

}