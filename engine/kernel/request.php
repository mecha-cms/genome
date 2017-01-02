<?php

class Request extends Genome {

    public static $config = [
        'session' => ['request' => 'mecha.request']
    ];

    protected static function alter($kind) {
        return Anemon::alter(strtolower($kind), [
            'get' => isset($_GET) ? $_GET : [],
            'post' => isset($_POST) ? $_POST : []
        ], []);
    }

    public static function any($kind, $key = null, $fail = "") {
        $data = self::alter($kind);
        if (isset($key)) {
            $o = e(Anemon::get($data, $key, $fail));
            return $o === 0 || !empty($o) ? $o : $fail;
        }
        return e(!empty($data) ? $data : $fail);
    }

    public static function get($key, $fail = "") {
        return self::any('get', $key, $fail);
    }

    public static function post($key, $fail = "") {
        return self::any('post', $key, $fail);
    }

    // save state
    public static function save($kind, $k = null, $v = "") {
        $data = self::alter($kind);
        if (!is_array($k)) {
            $k = [$k => $v];
        }
        $memo = Session::get(self::$config['session']['request'], []);
        Session::set(self::$config['session']['request'], Anemon::extend($memo, $k));
    }

    // restore state
    public static function restore($k = null, $fail = "") {
        $memo = Session::get(self::$config['session']['request'], []);
        self::delete($k);
        return isset($k) ? Anemon::get($memo, $k, $fail) : $memo;
    }

    // delete state
    public static function delete($k = null) {
        Session::reset(self::$config['session']['request'] . (isset($k) ? '.' . $k : ""));
    }

}