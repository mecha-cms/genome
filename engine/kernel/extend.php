<?php

class Extend extends Genome {

    // Cache!
    protected static $state;

    public static function exist(string $id, $active = true) {
        $path = constant(u(static::class));
        if ($active) {
            return is_file($path . DS . $id . DS . 'index.php');
        }
        return is_dir($path . DS . $id);
    }

    public static function state(...$lot) {
        $c = static::class;
        $n = basename(array_shift($lot));
        $parts = explode(':', $n, 2);
        $id = $parts[0];
        $key = array_shift($lot);
        $state = constant(u($c)) . DS . $id . DS . 'lot' . DS . 'state' . DS . ($parts[1] ?? 'config') . '.php';
        $id = strtr($id, '.', '/');
        if (!empty(self::$state[$c][$n])) {
            $state = self::$state[$c][$n];
        } else {
            extract(Lot::get(), EXTR_SKIP);
            $state = is_file($state) ? include $state : [];
            $state = Hook::fire(c2f($c, '_', '/') . '.state.' . $n, [$state], null, $c);
            self::$state[$c][$n] = $state;
        }
        if (is_array($key)) {
            return extend($key, $state);
        }
        return isset($key) ? ($state[$key] ?? null) : $state;
    }

}