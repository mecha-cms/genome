<?php

class Page extends Genome {

    public $lot = [];
    public $lot_alt = [];

    private $prefix = "";

    public function __construct($input = null, $lot = [], $NS = 'page') {
        $t = File::T($input, time());
        $date = date(DATE_WISE, $t);
        $this->lot_alt = array_replace(a(Config::get('page', [])), [
            'time' => $date,
            'update' => $date,
            'slug' => Path::N($input),
            'state' => Path::X($input),
            'id' => (string) $t,
            'url' => To::url($input)
        ], $lot);
        $this->prefix = $NS . '.';
        $this->lot = array_replace($lot, is_array($input) ? $input : ['path' => $input]);
        if (!array_key_exists('date', $this->lot)) {
            $this->lot['date'] = new Date(File::open(Path::F($input) . DS . 'time.data')->read($this->lot_alt['time']));
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
            if (isset($this->lot['path'])) {
                if ($data = File::open(Path::F($this->lot['path']) . DS . $key . '.data')->get()) {
                    $this->lot[$key] = e($data);
                } else if ($page = File::open($this->lot['path'])->read()) {
                    $this->lot = array_replace($this->lot, $this->lot_alt, e(self::apart($page)));
                }
            }
            if (!array_key_exists($key, $this->lot)) {
                $this->lot[$key] = array_key_exists($key, $this->lot_alt) ? e($this->lot_alt[$key]) : null;
            }
        }
        return Hook::NS($this->prefix . $key, [$this->lot[$key], $this->lot]);
    }

    public function __unset($key) {
        unset($this->lot[$key]);
    }

    public function __toString() {
        return file_get_contents(self::$data['path']);
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
            // Do data…
            foreach (explode(self::$v[4], $input[0]) as $v) {
                $v = explode(self::$v[2], $v, 2);
                $data[self::v($v[0])] = e(self::v(isset($v[1]) ? $v[1] : false));
            }
            // Do content…
            $data['content'] = trim(isset($input[1]) ? $input[1] : "", "\n");
        }
        return $data;
    }

    public static function unite($input) {
        $data = [];
        $content = "";
        if (isset($input['content'])) {
            $content = $input['content'];
            unset($input['content']);
        }
        foreach ($input as $k => $v) {
            $v = self::x(s($v));
            if ($v && strpos($v, "\n") !== false) {
                $v = json_encode($v); // contains line–break
            }
            $data[] = self::x($k) . self::$v[2] . $v;
        }
        return ($data ? self::$v[0] . implode(N, $data) . self::$v[1] : "") . ($content ? N . N . $content : "");
    }

    protected static $data = [];

    public static function open($path, $lot = [], $NS = 'page') {
        self::$data = ['path' => $path];
        return new static($path, $lot, $NS);
    }

    public static function data($input, $fn = null, $NS = 'page') {
        if (!is_array($input)) {
            if (is_callable($fn)) {
                self::$data[$input] = call_user_func($fn, self::$data);
                $input = [];
            } else {
                $input = ['content' => $input];
            }
        }
        self::$data = $data = array_replace(self::$data, $input);
        foreach ($data as $k => $v) {
            if ($v === false) unset(self::$data[$k], $data[$k]);
        }
        unset($data['path']);
        return new static(null, $data, $NS);
    }

    public function get($key, $fail = null, $NS = 'page') {
        if (is_array($key)) {
            $output = [];
            foreach ($key as $k => $v) {
                $output[$k] = $this->{$k}($v);
            }
            return $output;
        }
        return $this->{$key}($fail);
    }

    public static function saveTo($path, $consent = 0600) {
        unset(self::$data['path']);
        File::write(self::unite(self::$data))->saveTo($path, $consent);
    }

    public static function save($consent = 0600) {
        return self::saveTo(self::$data['path'], $consent);
    }

}