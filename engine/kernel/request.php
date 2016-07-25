<?php

/**
 * ===========================================================
 *  PAGE REQUEST
 * ===========================================================
 *
 * -- CODE: --------------------------------------------------
 *
 *    if (Request::post()) {
 *        echo Request::post('name');
 *        echo Request::post('foo.bar');
 *        echo Request::post('foo.bar', 'Failed.');
 *    }
 *
 * -----------------------------------------------------------
 *
 *    if (Request::get()) { ... }
 *
 * -----------------------------------------------------------
 *
 *    if (Request::method('post')) { ... }
 *
 *    if (Request::method() === 'post') { ... }
 *
 * -----------------------------------------------------------
 *
 */

class Request extends __ {

    public static function post($param = null, $fail = false, $str_eval = true) {
        if (is_null($param)) {
            return $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST) && !empty($_POST) ? ($str_eval ? Converter::strEval($_POST) : $_POST) : $fail;
        }
        $output = Mecha::GVR($_POST, $param, $fail);
        return $output === '0' || !empty($output) ? ($str_eval ? Converter::strEval($output) : $output) : $fail;
    }

    public static function get($param = null, $fail = false, $str_eval = true) {
        if (is_null($param)) {
            return $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET) && !empty($_GET) ? ($str_eval ? Converter::strEval($_GET) : $_GET) : $fail;
        }
        $output = Mecha::GVR($_GET, $param, $fail);
        return $output === '0' || !empty($output) ? ($str_eval ? Converter::strEval($output) : $output) : $fail;
    }

    public static function method($method = null, $fail = false) {
        if (is_null($method)) {
            return strtolower($_SERVER['REQUEST_METHOD']);
        }
        return strtolower($_SERVER['REQUEST_METHOD']) === strtolower($method) ? strtolower($method) : $fail;
    }

}