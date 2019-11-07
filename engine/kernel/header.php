<?php

final class Header extends Genome {

    public static function get($key = null) {
        if (isset($key)) {
            $v = $_SERVER['HTTP_' . strtr(strtoupper($key), '-', '_')] ?? null;
            if (null === $v) {
                if (function_exists('apache_response_headers')) {
                    $v = apache_response_headers()[$key] ?? null;
                }
                if (null === $v) {
                    foreach (headers_list() as $h) {
                        if (0 === stripos($h, $key . ': ')) {
                            $h = explode(':', $h, 2);
                            $v = isset($h[1]) && "" !== $h[1] ? e(trim($h[1])) : null;
                            break;
                        }
                    }
                }
            }
            return e($v);
        }
        $out = [];
        if (function_exists('apache_response_headers')) {
            $out = e(apache_response_headers());
        }
        foreach (headers_list() as $v) {
            $v = explode(':', $v, 2);
            $out[$v[0]] = isset($v[1]) && "" !== $v[1] ? e(trim($v[1])) : null;
        }
        return $out;
    }

    public static function let($key = null) {
        if (isset($key)) {
            if (is_array($key)) {
                foreach ($key as $v) {
                    self::let($v);
                }
            } else {
                header_remove($key);
                unset($_SERVER['HTTP_' . strtr(strtoupper($key), '-', '_')]);
            }
        } else {
            header_remove();
            foreach ($_SERVER as $k => $v) {
                if (0 === strpos($k, 'HTTP_')) {
                    unset($_SERVER[$k]);
                }
            }
        }
    }

    public static function set($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                header($k . ': ' . $v);
            }
        } else {
            header($key . ': ' . $value);
        }
    }

}