<?php

class Extend extends Genome {

    // Cache!
    protected static $state = [];

    public static function exist(string $id, $active = true) {
        $path = constant(u(static::class));
        if ($active) {
            return is_file($path . DS . $id . DS . 'index.php');
        }
        return is_dir($path . DS . $id);
    }

    public static function state(...$lot) {
        $c = static::class;
        $q = basename(array_shift($lot));
        $parts = explode(':', $q);
        $id = $parts[0];
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: constant(u($c));
        $state = $folder . DS . $id . DS . 'lot' . DS . 'state' . DS . ($parts[1] ?? 'config') . '.php';
        $id = strtr($id, '.', '/');
        if (!file_exists($state)) {
            return is_array($key) ? $key : $fail;
        }
        extract(Lot::get(), EXTR_SKIP);
        if (!empty(self::$state[$c][$q])) {
            $state = self::$state[$c][$q];
        } else {
            $state = include $state;
        }
        $state = self::$state[$c][$q] = Hook::fire(c2f($c, '_', '/') . '.state.' . $q, [$state], null, $c);
        if (is_array($key)) {
            return extend($key, $state);
        }
        return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
    }

}