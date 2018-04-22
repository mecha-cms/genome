<?php

class Shield extends View {

    protected static $state = [];

    public static function attach($input, $fail = false) {
        self::load($input, $fail);
    }

    public static function abort($code = 404, $fail = false) {
        $i = explode('/', str_replace(DS, '/', (string) $code))[0];
        $i = is_numeric($i) ? $i : '404';
        HTTP::status((int) $i);
        self::load($code, $fail);
    }

    public static function exist($input, $fail = false) {
        return Folder::exist(constant(u(static::class)) . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $c = static::class;
        $id = basename(array_shift($lot));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: constant(u($c));
        $state = $folder . DS . $id . DS . 'state' . DS . 'config.php';
        $id = str_replace('.', '/', $id);
        if (!file_exists($state)) {
            return is_array($key) ? $key : $fail;
        }
        $state = isset(self::$state[$c][$id]) ? self::$state[$c][$id] : include $state;
        $state = Hook::fire(__c2f__($c, '_', '/') . '.state.' . $id, [$state]);
        if (is_array($key)) {
            return array_replace_recursive($key, $state);
        }
        return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
    }

}