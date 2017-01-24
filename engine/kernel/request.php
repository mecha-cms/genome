<?php

class Request extends Genome {

    public static $config = [
        'session' => ['request' => 'Mecha\Request']
    ];

    public static function any($id, $key = null, $fail = null, $eval = true) {
        $data = $GLOBALS['_' . strtoupper($id)];
        $data = isset($data) ? $data : [];
        if (isset($key)) {
            $o = Anemon::get($data, $key, $fail);
            $o = $eval ? e($o) : $o;
            return $o === 0 || $o === '0' || !empty($o) ? $o : $fail;
        }
        return !empty($data) ? ($eval ? e($data) : $data) : $fail;
    }

    public static function is($id = null, $key = null) {
        $s = strtoupper($_SERVER['REQUEST_METHOD']);
        if (isset($id)) {
            $id = strtoupper($id);
            if (isset($key)) {
                return array_key_exists($key, $GLOBALS['_' . $id]);
            }
            return $id === $s;
        }
        return $s;
    }

    // `GET` property
    public static function get($key = null, $fail = null, $eval = true) {
        return self::any('GET', $key, $fail, $eval);
    }

    // `POST` property
    public static function post($key = null, $fail = null, $eval = true) {
        return self::any('POST', $key, $fail, $eval);
    }

    // `SERVER` property
    public static function server($key = null, $fail = null, $eval = true) {
        return self::any('SERVER', $fail, $eval);
    }

    public static function set($id, $key, $value = null) {
        Anemon::set($GLOBALS['_' . strtoupper($id)], $key, $value);
        return new static;
    }

    public static function reset($id, $key = null) {
        Anemon::reset($GLOBALS['_' . strtoupper($id)], $key);
        return new static;
    }

    public static function extend($id, $keys = []) {
        foreach ($keys as $k => $v) {
            self::set($id, $k, $v);
        }
        return new static;
    }

    // save state
    public static function save($id, $key = null, $value = null) {
        $id = strtoupper($id);
        $data = self::any($id, null, []);
        if (isset($key)) {
            $key = [$key => $value];
        } else {
            $key = $data;
        }
        $s = self::$config['session']['request'] . Anemon::NS . $id;
        $cache = Session::get($s, []);
        Session::set($s, Anemon::extend($cache, $key));
        return new static;
    }

    // restore state
    public static function restore($id, $key = null, $fail = null) {
        $id = strtoupper($id);
        $cache = Session::get(self::$config['session']['request'] . Anemon::NS . $id, []);
        if (isset($key)) {
            self::delete($id, $key);
            return Anemon::get($cache, $key, $fail);
        }
        self::delete($id);
        return $cache;
    }

    // delete state
    public static function delete($id, $key = null) {
        $id = strtoupper($id);
        Session::reset(self::$config['session']['request'] . Anemon::NS . $id . (isset($key) ? Anemon::NS . $key : ""));
        return new static;
    }

}