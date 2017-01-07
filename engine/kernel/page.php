<?php

class Page extends Genome {

    protected static $data = [];
    protected static $path = "";
    protected static $shift = "";

    // `0`: draft
    // `1`: page
    // `2`: archive
    public static $states = ['draft', 'page', 'archive'];
    public static $i = ['time', 'kind', 'slug', 'state'];

    public static $v = ["---\n", "\n...", ': ', '- ', "\n"];
    public static $x = ['&#45;&#45;&#45;&#10;', '&#10;&#46;&#46;&#46;', '&#58;&#32;', '&#45;&#32;', '&#10;'];

    // Escape …
    public static function x($s) {
        return str_replace(self::$v, self::$x, $s);
    }

    // Un–Escape …
    public static function v($s) {
        return str_replace(self::$x, self::$v, $s);
    }

    // Open …
    public static function open($path, $shift = "") {
        self::$path = $path;
        self::$shift = $shift;
        self::apart();
        return new static;
    }

    // Unite …
    public static function unite($input = null) {
        if (!isset($input)) {
            $input = self::$data;
        }
        $meta = [];
        $data = "";
        if (isset($input['content'])) {
            $data = $input['content'];
            unset($input['content']);
        }
        foreach ($input as $k => $v) {
            $meta[] = self::x($k) . self::$v[2] . self::x(s($v));
        }
        return ($meta ? self::$v[0] . implode(N, $meta) . self::$v[1] : "") . ($data ? N . N . $data : "");
    }

    // Apart …
    public static function apart($input = null) {
        extract(Lot::get(null, []));
        $data = [];
        if (!isset($input)) {
            $input = self::$path;
        }
        if (is_string($input) || is_file($input)) {
            if (is_file($input)) {
                $data = Get::page($input);
                $input = file_get_contents($input);
            }
            $input = n($input);
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
        } else if (__is_anemon__($input)) {
            $data = a($input); // should be an array input
        }
        if (!array_key_exists('time', $data) && isset($data['update'])) {
            $data['time'] = $data['update'];
        }
        $o = a($config->page);
        $data = Anemon::extend($o, $data);
        $shift = self::$shift;
        $url = __url__();
        $url_r = PAGE . ($shift ? DS . $shift : "");
        $url_path = To::url(str_replace([$url_r . DS, $url_r], "", Path::D($data['path'])));
        $data['url'] = $url['url'] . ($url_path ? '/' . $url_path : "") . '/' . $data['slug'];
        $data['date'] = new Date($data['time']);
        self::$data = $data;
        return new static;
    }

    // Create data …
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
        // Pre-defined page data …
        if ($output) {
            foreach ($output as $k => $v) {
                if (strpos($k, '__') !== 0 && !array_key_exists('__' . $k, $output)) {
                    $output['__' . $k] = $v;
                }
            }
        }
        // Load page data …
        return self::_meta_hook(array_merge($output, self::$data), $output, $NS);
    }

    // Read specific page property
    public static function get($key = null, $fail = "", $NS = 'page') {
        if (!isset($key)) {
            $data = self::$data;
            return self::_meta_hook($data, $data, $NS);
        }
        $data = Get::page($input = self::$path, null, [], $key);
        $input = Path::D($input) . DS . Path::N($input);
        if ($input === DS || is_dir($input)) {
            $data[$key] = File::open($input . DS . $key . '.data')->read(array_key_exists($key, self::$data) ? self::$data[$key] : false);
        } else {
            $data[$key] = false;
        }
        if ($data[$key] === false && file_exists(self::$path)) {
            self::open(self::$path);
            Anemon::extend($data, self::$data);
            if (!array_key_exists($key, $data) || $data[$key] !== '0' && empty($data[$key])) {
                $data[$key] = $fail;
            }
        }
        $output = e(self::_meta_hook($data, $data, $NS)[$key]);
        return $output !== false ? $output : $fail;
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
        if (is_string($fail) && strpos($fail, '~') === 0) {
            return call_user_func(substr($fail, 1), array_key_exists($key, $this->lot) ? o($this->lot[$key]) : $fail_alt);
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

    public function __unset($key) {
        unset($this->lot[$key]);
    }

    public function __toString() {
        return $this->unite();
    }

}