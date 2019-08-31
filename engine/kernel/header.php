<?php

final class Header extends Genome {

    public static function get($key = null) {
        if (isset($key)) {
            $v = $_SERVER['HTTP_' . strtr(strtoupper($key), '-', '_')] ?? null;
            if ($v === null) {
                if (function_exists('apache_response_headers')) {
                    $v = apache_response_headers()[$key] ?? null;
                }
                if ($v === null) {
                    foreach (headers_list() as $v) {
                        if (strpos($v, $key . ': ') === 0) {
                            $v = explode(':', $v, 2);
                            $v = isset($v[1]) && $v[1] !== "" ? e(trim($v[1])) : null;
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
            $out[$v[0]] = isset($v[1]) && $v[1] !== "" ? e(trim($v[1])) : null;
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
                if (strpos($k, 'HTTP_') === 0) {
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

    public static function status(int $i = null) {
        if (isset($i)) {
            http_response_code($i);
        }
        return http_response_code();
    }

    public static function type(string $type, array $lot = []) {
        foreach ($lot as $k => $v) {
            $type .= '; ' . $k . '=' . $v;
        }
        header('Content-Type: ' . $type);
    }

}