<?php

class Request extends Socket {

    public function post($var = null, $fail = false, $eval = true) {
        if ($var === null) {
            return $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST) && !Is::void($_POST) ? ($eval ? e($_POST) : $_POST) : $fail;
        }
        $o = Anemon::get($_POST, $var, $fail);
        return !Is::void($o) ? ($eval ? e($o) : $o) : $fail;
    }

    public function get($var = null, $fail = false, $eval = true) {
        if ($var === null) {
            return $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET) && !Is::void($_GET) ? ($eval ? e($_GET) : $_GET) : $fail;
        }
        $o = Anemon::get($_GET, $var, $fail);
        return !Is::void($o) ? ($eval ? e($o) : $o) : $fail;
    }

    public function method($x = null, $fail = false) {
        if ($x === null) {
            return strtolower($_SERVER['REQUEST_METHOD']);
        }
        return strtolower($_SERVER['REQUEST_METHOD']) === strtolower($x) ? strtolower($x) : $fail;
    }

}