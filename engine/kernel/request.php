<?php

class Request extends Genome {

    public $data;
    public $header;
    public $type;
    public $url;

    public function __construct(...$lot) {
        $this->type = ucfirst(strtolower(array_shift($lot)));
        $this->url = $url = array_shift($lot);
        if (false !== strpos($url, '?')) {
            $this->data = From::query(explode('?', $url)[1]);
        }
    }

    public function header($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->header[$k] = $v;
            }
        } else {
            $this->header[$key] = $value;
        }
    }

    public function send(array $data) {
        $data = array_replace_recursive($this->data ?? [], $data);
        return fetch($this->url . ($data ? To::query($data) : ""), $this->header, $this->type);
    }

    public static function get($key = null) {
        $a = $GLOBALS['_' . strtoupper(static::class)] ?? [];
        return e(isset($key) ? get($a, $key) : ($a ?? []));
    }

    public static function is(string $name = null, string $key = null) {
        $r = strtoupper($_SERVER['REQUEST_METHOD']);
        if (isset($name)) {
            $name = strtoupper($name);
            if (isset($key)) {
                $a = $GLOBALS['_' . $name] ?? [];
                return null !== get($a, $key);
            }
            return $name === $r;
        }
        return ucfirst(strtolower($r));
    }

    public static function let($key = null) {
        $k = strtoupper(static::class);
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key)) {
            let($GLOBALS['_' . $k], $key);
        } else {
            $GLOBALS['_' . $k] = [];
        }
    }

    public static function set(string $key, $value) {
        set($GLOBALS['_' . strtoupper(static::class)], $key, $value);
    }

}