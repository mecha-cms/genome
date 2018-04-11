<?php

class Cookie extends Genome {

    public static function set($key, $value = "", $config = []) {
        if (is_numeric($config)) {
            $config = ['expire' => (int) $config];
        }
        $config = array_replace([
            'expire' => 1, // 1 day
            'path' => '/',
            'domain' => "",
            'secure' => false,
            'http_only' => false
        ], $config);
        $config['expire'] = time() + (60 * 60 * 24 * $config['expire']);
        // TODO: update cookie value in array
        setcookie('_' . dechex(crc32(static::class . ':' . $key)), base64_encode(json_encode($value)), ...array_values($config));
        return new static;
    }

    public static function get($key = null, $fail = null) {
        if (!isset($key)) {
            $o = [];
            foreach ($_COOKIE as $k => $v) {
                $o[$k] = e(strpos($k, '_') === 0 ? json_decode(base64_decode($v)) : $v);
            }
            return $o;
        }
        $key = '_' . dechex(crc32(static::class . ':' . $key));
        $value = json_decode(base64_decode(isset($_COOKIE[$key]) ? $_COOKIE[$key] : 'bnVsbA=='));
        return $value !== null ? $value : $fail;
    }

    public static function reset($key = null) {
        if (!isset($key)) {
            foreach ($_COOKIE as $k => $v) {
                setcookie($k, null, -1);
            }
        } else if (is_array($key)) {
            foreach ($key as $v) {
                self::reset($v);
            }
        } else {
            setcookie('_' . dechex(crc32(static::class . ':' . $key)), null, -1);
        }
        return new static;
    }

}