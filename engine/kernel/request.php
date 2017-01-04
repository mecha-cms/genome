<?php

class Request extends Genome {

    public static $config = [
        'session' => ['request' => 'mecha.request']
    ];

    public static function any($id, $key = null, $fail = "") {
        $data = $GLOBALS['_' . strtoupper($id)];
        $data = isset($data) ? $data : [];
        if (isset($key)) {
            $o = e(Anemon::get($data, $key, $fail));
            return $o === 0 || !empty($o) ? $o : $fail;
        }
        return e(!empty($data) ? $data : $fail);
    }

    // `GET` request
    public static function get($key, $fail = "") {
        return self::any('get', $key, $fail);
    }

    // `POST` request
    public static function post($key, $fail = "") {
        return self::any('post', $key, $fail);
    }

    // save state
    public static function save($id, $k = null, $v = "") {
        $data = self::any($id);
        if (!is_array($k)) {
            $k = [$k => $v];
        }
        $memo = Session::get(self::$config['session']['request'], []);
        Session::set(self::$config['session']['request'], Anemon::extend($memo, $k));
    }

    // restore state
    public static function restore($k = null, $fail = "") {
        $memo = Session::get(self::$config['session']['request'], []);
        if (isset($k)) {
            self::delete($k);
            return Anemon::get($memo, $k, $fail);
        }
        self::delete();
        return $memo;
    }

    // delete state
    public static function delete($k = null) {
        Session::reset(self::$config['session']['request'] . (isset($k) ? '.' . $k : ""));
    }

}