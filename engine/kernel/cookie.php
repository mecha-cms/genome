<?php

class Cookie extends Genome {

    public static function set($key, $value = "", $config = []) {
        if (is_numeric($config)) {
            $config = ['expire' => (int) $config];
        }
        $cc = [
            'expire' => 1,
            'path' => '/',
            'domain' => "",
            'secure' => false,
            'http_only' => false
        ];
        Anemon::extend($cc, $config);
        $cc = array_values($cc);
        $cc[0] = time() + 60 * 60 * 24 * $cc[0]; // 1 day
        if (strpos($key, '.') !== false) {
            $a = explode('.', $key);
            $n = array_shift($a);
            $o = e($_COOKIE[$n] ?? []);
            if (__is_anemon__($value)) $value = a($value);
            Anemon::set($o, implode('.', $a), $value);
            $value = $o;
        }
        array_unshift($cc, [$key, To::base64(To::json($value))]);
        call_user_func_array('setcookie', $cc);
        $cc[0] = '__' . $cc[0];
        $cc[1] = [$cc[2], $cc[3], $cc[4], $cc[5], $cc[6]];
        $cc[3] = '/';
        $cc[4] = "";
        $cc[5] = $cc[6] = false;
        call_user_func_array('setcookie', $cc);
    }

    public static function get($key = null, $fail = "") {
        $c = isset($_COOKIE) ? e($_COOKIE) : $fail;
        if (!isset($key)) return $c;
        $v = Anemon::get($c, $key, $fail);
        return !is_array($v) && isset($o = To::anemon(From::base64($v))) ? $o : $v;
    }

    public static function reset($key = null) {
        if (!isset($key)) {
            $_COOKIE = [];
            foreach (explode(';', isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : "") as $v) {
                $c = explode('=', $v, 2);
                $n = trim($c[0]);
                setcookie($n, null, -1);
                setcookie($n, null, -1, '/');
            }
        } else {
            if (strpos($key, '.') !== false) {
                $a = explode('.', $key);
                if (isset($_COOKIE[$a[0]])) {
                    $o = e($_COOKIE[$a[0]]);
                    Anemon::reset($o, $key);
                    foreach ($o as $k => $v) {
                        $_COOKIE[$a[0]][$k] = is_array($v) ? To::base64(To::json($v)) : $v;
                    }
                    $cc = e($_COOKIE['__' . $a[0]]);
                    array_unshift($cc, [$a[0], To::base64(To::json($o))]);
                    call_user_func_array('setcookie', $cc);
                }
            } else {
                unset($_COOKIE[$key]);
                setcookie($key, null, -1);
                setcookie($key, null, -1, '/');
            }
        }
    }

}