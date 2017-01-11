<?php

class Page extends Genome {

    public $lot = [];
    public $lot_alt = [];

    private $prefix = "";

    public function __construct($input = null, $lot = [], $NS = 'page') {
        extract(Lot::get(null, []));
        if ($input) {
            $t = is_file($input) ? filemtime($input) : time();
            $date = date(DATE_WISE, $t);
            $this->prefix = $NS . Anemon::NS;
            $this->lot = ['path' => $input];
            $this->lot_alt = array_replace(a($config->page), $lot, [
                'time' => $date,
                'update' => $date,
                'kind' => [0],
                'slug' => Path::N($input),
                'state' => Path::X($input),
                'id' => (string) $t,
                'url' => To::url($input)
            ]);
        }
    }

    public function __call($key, $lot) {
        $fail = array_shift($lot);
        $fail_alt = array_shift($lot);
        $x = $this->__get($key);
        if (is_string($fail) && strpos($fail, '~') === 0) {
            return call_user_func(substr($fail, 1), $x !== null ? $x : $fail_alt);
        } else if ($fail instanceof \Closure) {
            return call_user_func($fail, $x !== null ? $x : $fail_alt);
        }
        return $x !== null ? $x : $fail;
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        if (!array_key_exists($key, $this->lot)) {
            if ($data = File::open(Path::F($this->lot['path']) . DS . $key . '.data')->get()) {
                $this->lot[$key] = e($data);
            } else if ($page = File::open($this->lot['path'])->read()) {
                $this->lot = array_replace($this->lot, $this->lot_alt, e(self::apart($page)));
            }
            if (!array_key_exists($key, $this->lot)) {
                $this->lot[$key] = array_key_exists($key, $this->lot_alt) ? e($this->lot_alt[$key]) : null;
            }
        }
        return $this->_hook($key, $this->lot[$key]);
    }

    public function __toString() {
        return file_get_contents(self::$path);
    }

    protected function _hook($key, $input) {
        return Hook::NS($this->prefix . $key, [is_array($input) ? [$input] : $input, $this->lot]);
    }

    public static $v = ["---\n", "\n...", ': ', '- ', "\n"];
    public static $x = ['&#45;&#45;&#45;&#10;', '&#10;&#46;&#46;&#46;', '&#58;&#32;', '&#45;&#32;', '&#10;'];

    public static function v($s) {
        return str_replace(self::$x, self::$v, $s);
    }

    public static function x($s) {
        return str_replace(self::$v, self::$x, $s);
    }

    public static function apart($input) {
        $input = n($input);
        $data = [];
        if (strpos($input, self::$v[0]) !== 0) {
            $data['content'] = self::v($input);
        } else {
            $input = str_replace([X . self::$v[0], X], "", X . $input . N . N);
            $input = explode(self::$v[1] . N . N, $input, 2);
            // Do data …
            foreach (explode(self::$v[4], $input[0]) as $v) {
                $v = explode(self::$v[2], $v, 2);
                $data[self::v($v[0])] = e(self::v(isset($v[1]) ? $v[1] : false));
            }
            // Do content …
            $data['content'] = trim(isset($input[1]) ? $input[1] : "", "\n");
        }
        return $data;
    }

    public static function unite($input) {
        $data = [];
        $content = "";
        if (isset($input['content'])) {
            $content = $input['content'];
            unset($data['content']);
        }
        foreach ($input as $k => $v) {
            $data[] = self::x($k) . self::$v[2] . self::x(s($v));
        }
        return ($data ? self::$v[0] . implode(N, $data) . self::$v[1] : "") . ($content ? N . N . $content : "");
    }

    protected static $data = [];

    public static function open($path) {
        self::$data = ['path' => $path];
        return new static($path);
    }

    public static function data($input, $fn = null) {
        if (!is_array($input)) {
            if (is_callable($fn)) {
                self::$data[$input] = call_user_func($fn, self::$data);
                $input = [];
            } else {
                $input = ['content' => $input];
            }
        }
        self::$data = array_replace(self::$data, $input);
        foreach (self::$data as $k => $v) {
            if ($v === false) unset(self::$data[$k]);
        }
        return new static;
    }

    public static function read($output = [], $NS = 'page') {
        $page = new static(self::$data['path'], [], $NS);
        $o = [];
        foreach ($output as $k => $v) {
            $o[$k] = $page->{$k}($v);
        }
        return $o;
    }

    public static function get($key, $fail = null, $NS = 'page') {
        if (is_array($key)) {
            $output = [];
            $page = new static(self::$data['path'], [], $NS);
            foreach ($key as $k => $v) {
                $output[$k] = $page->{$k}($v);
            }
            return $output;
        }
        return (new static(self::$data['path'], [], $NS))->{$key}($fail);
    }

    public static function saveTo($path, $consent = 0600) {
        File::write(self::unite(self::$data))->saveTo($path, $consent);
    }

    public static function save($consent = 0600) {
        return self::saveTo(self::$path, $consent);
    }

}