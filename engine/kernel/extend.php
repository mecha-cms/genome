<?php

class Extend extends Genome {

    protected static $state = [];

    public static function exist(string $input, $fail = false) {
        return Folder::exist(constant(u(static::class)) . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $c = static::class;
        $id = basename(array_shift($lot));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: constant(u($c));
        $state = $folder . DS . $id . DS . 'lot' . DS . 'state' . DS . 'config.php';
        $id = str_replace('.', '/', $id);
        if (!file_exists($state)) {
            return is_array($key) ? $key : $fail;
        }
        $state = self::$state[$c][$id] ?? include $state;
        $state = Hook::fire(c2f($c, '_', '/') . '.state.' . $id, [$state]);
        if (is_array($key)) {
            return array_replace_recursive($key, $state);
        }
        return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
    }

}