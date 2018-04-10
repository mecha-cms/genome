<?php

class Cookie extends Genome {

    public static function set($key, $value = "", $config = []) {
        $c = static::class;
        if (is_numeric($config)) {
            $config = ['expire' => (int) $config];
        }
        $config = array_replace([
            'expire' => 1,
            'path' => '/',
            'domain' => "",
            'secure' => false,
            'http_only' => false
        ], $config);
        $config = array_values($config);
        $config[0] = time() + 60 * 60 * 24 * $config[0]; // 1 day
        $v = [];
        Anemon::set($v, $key, $value);
        setcookie($c, base64_encode(json_encode([
            0 => $v, // the cookie value
            1 => $config // the cookie setting(s)
        ])), ...$config);
        return new static;
    }

    public static function get($key = null, $fail = null, $i = 0) {
        $value = json_decode(base64_decode(HTTP::cookie(static::class, 'W10=')), true);
        $value = array_replace([$i => []], (array) $value);
        return isset($key) ? Anemon::get($value[$i], $key, $fail) : $value[$i];
    }

    public static function reset($key = null) {
        $c = static::class;
        if (!isset($key)) {
            setcookie($c, null, -1);
            setcookie($c, null, -1, '/');
            return new static;
        }
        $value = self::get(null, [], 0);
        $config = self::get(null, [], 1);
        if (is_array($key)) {
            foreach ($key as $v) {
                self::reset($v);
            }
        } else {
            Anemon::reset($value, $key);
            self::set($key, $value, $config);
        }
        return new static;
    }

}