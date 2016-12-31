<?php

class Page extends Genome {

    protected static $data = [];
    protected static $path = "";
    protected static $shift = "";

    // 0: draft
    // 1: page
    // 2: archive
    public static $states = ['draft', 'page', 'archive'];
    public static $i = ['time', 'kind', 'slug', 'state'];

    public static $v = ["---\n", "\n...", ': ', '- ', "\n"];
    public static $x = ['&#45;&#45;&#45;&#10;', '&#10;&#46;&#46;&#46;', '&#58;&#32;', '&#45;&#32;', '&#10;'];

    // Escape ...
    public static function x($s) {
        return str_replace(self::$v, self::$x, $s);
    }

    // Un-Escape ...
    public static function v($s) {
        return str_replace(self::$x, self::$v, $s);
    }

    public static function open($path, $shift = "") {
        self::$path = $path;
        self::$shift = $shift;
        self::apart();
        return new static;
    }

    // Apart ...
    public static function apart($input = null) {
        global $config;
        $data = [];
        if (!isset($input)) {
            $input = self::$path;
        }
        if (is_file($input)) {
            $data = Get::page($input);
            $input = n(file_get_contents($input));
            if (strpos($input, self::$v[0]) !== 0) {
                $data['content'] = self::v($input);
            } else {
                $input = str_replace([X . self::$v[0], X], "", X . $input . N . N);
                $input = explode(self::$v[1] . N . N, $input, 2);
                // Do data ...
                foreach (explode(self::$v[4], $input[0]) as $v) {
                    $v = explode(self::$v[2], $v, 2);
                    $data[self::v($v[0])] = e(self::v(isset($v[1]) ? $v[1] : false));
                }
                // Do content ...
                $data['content'] = trim(isset($input[1]) ? $input[1] : "");
            }
            $s = self::$path;
            $input = Path::D($s) . DS . Path::N($s);
            if (is_dir($input)) {
                foreach (g($input, 'data') as $v) { // get all `*.data` file(s) from a folder
                    $a = Path::N($v);
                    $b = file_get_contents($v);
                    $data[$a] = e($b);
                }
            }
        }
        if (!array_key_exists('time', $data)) {
            $data['time'] = $data['update'];
        }
        $data = Anemon::extend($config->page, $data);
        $shift = self::$shift;
        $url = __url__();
        $url_r = PAGE . ($shift ? DS . $shift : "");
        $url_path = To::url(str_replace([$url_r . DS, $url_r], "", Path::D($data['path'])));
        $data['url'] = $url['url'] . ($url_path ? '/' . $url_path : "") . '/' . $data['slug'];
        $data['date'] = new Date($data['time']);
        self::$data = $data;
        return new static;
    }

    // Unite ...
    public static function unite() {
        $meta = [];
        $data = "";
        if (isset(self::$data['content'])) {
            $data = self::$data['content'];
            unset(self::$data['content']);
        }
        foreach (self::$data as $k => $v) {
            $meta[] = self::x($k) . self::$v[2] . self::x(s($v));
        }
        return ($meta ? self::$v[0] . implode(N, $meta) . self::$v[1] : "") . ($data ? N . N . $data : "");
    }

    // Create data ...
    public static function data($a, $fn = null) {
        if (!is_array($a)) {
            if (is_callable($fn)) {
                self::$data[$a] = call_user_func($fn, self::$data);
                $a = [];
            } else {
                $a = ['content' => $a];
            }
        }
        Anemon::extend(self::$data, $a);
        foreach (self::$data as $k => $v) {
            if ($v === false) unset(self::$data[$k]);
        }
        return new static;
    }

    // Read all page propert(y|ies)
    public static function read($output = [], $NS = 'page') {
        // Pre-defined page meta ...
        if ($output) {
            foreach ($output as $k => $v) {
                if (strpos($k, '__') !== 0 && !array_key_exists('__' . $k, $output)) {
                    $output['__' . $k] = $v;
                }
            }
        }
        // Load page meta ...
        return self::_meta_hook(array_merge($output, self::$data), $output, $NS);
    }

    // Read specific page property
    public static function get($key, $fail = "", $NS = 'page') {
        $data = Get::page($input = self::$path);
        $input = Path::D($input) . DS . Path::N($input);
        if (is_dir($input)) {
            $data[$key] = File::open($input . DS . $key . '.data')->read(false);
        } else {
            $data[$key] = false;
        }
        if ($data[$key] === false && file_exists(self::$path)) {
            self::open(self::$path)->apart();
            $data[$key] = array_key_exists($key, self::$data) ? self::$data[$key] : $fail;
        }
        $output = self::_meta_hook($data, $data, $NS);
        return array_key_exists($key, $output) ? $output[$key] : $fail;
    }

    protected static function _meta_hook($input, $lot, $NS) {
        $input = Hook::NS($NS . Anemon::NS . 'input', [$input, $lot]);
        $output = Hook::NS($NS . Anemon::NS . 'output', [$input, $lot]);
        return $output;
    }

    public static function saveTo($path, $consent = 0600) {
        File::open($path)->write(self::unite())->save($consent);
    }

    public static function save($consent = 0600) {
        return self::saveTo(self::$path, $consent);
    }

    protected $lot = [];

    public function __construct($input = null, $shift = "", $lot = [], $NS = 'page') {
        if ($input) {
            if (!__is_anemon__($input)) {
                $input = self::open($input, $shift)->read($lot, $NS);
            }
            $this->lot = $input;
        }
    }

    public function __call($key, $lot) {
        $fail = array_shift($lot) ?: false;
        $fail_alt = array_shift($lot) ?: false;
        if (is_string($fail) && strpos($fail, 'fn::') === 0) {
            return call_user_func(substr($fail, 4), array_key_exists($key, $this->lot) ? o($this->lot[$key]) : $fail_alt);
        } else if ($fail instanceof \Closure) {
            return call_user_func($fail, array_key_exists($key, $this->lot) ? o($this->lot[$key]) : $fail_alt);
        }
        return array_key_exists($key, $this->lot) ? o($this->lot[$key]) : $fail;
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return array_key_exists($key, $this->lot) ? o($this->lot[$key]) : "";
    }

    public function __toString() {
        return $this->unite();
    }

}