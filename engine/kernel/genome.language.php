<?php namespace Genome;

class Language extends \Genome {

    public function __call($key, $lot) {
        return vsprintf(\Config::get('__i18n.' . $key, $key), $lot + [""]);
    }

    public function __set($key, $value = null) {
        \Config::set('__i18n.' . $key, $value);
    }

    public function __get($key) {
        return \Config::get('__i18n.' . $key, $key);
    }

    public function __toString() {
        return json_encode(\Config::get('__i18n'));
    }

    public function __invoke($fail = []) {
        return \Config::get('__i18n', o($fail));
    }

}