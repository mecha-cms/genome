<?php

class Page extends Genome {

    protected $lot = [];

    private $NS = "";
    private $hash = "";

    private static $page = []; // Cache!

    public function __construct($input = [], $lot = [], $NS = []) {
        $key = __c2f__(static::class, '_', '/');
        $this->NS = is_array($NS) ? array_replace(['*', $key], $NS) : $NS;
        $path = is_array($input) ? (isset($input['path']) ? $input['path'] : null) : $input;
        $id = $this->hash = md5(json_encode(array_merge((array) $lot, (array) $NS)) . $path);
        if (isset(self::$page[$id])) {
            $this->lot = self::$page[$id];
        } else {
            $n = $path ? Path::N($path) : null;
            $x = Path::X($path);
            $c = $m = $_SERVER['REQUEST_TIME'];
            if (file_exists($path)) {
                $c = filectime($path); // File creation time
                $m = filemtime($path); // File modification time
            }
            $this->lot = array_replace([
                'time' => date(DATE_WISE, $c),
                'update' => date(DATE_WISE, $m),
                'slug' => (string) $n,
                'title' => To::title($n), // Fake `title` data from the page’s file name
                'type' => u($x) . "", // Fake `type` data from the page’s file extension
                'state' => (string) $x,
                'id' => sprintf('%u', $c),
                'url' => To::URL($path)
            ], is_array($input) ? $input : [
                'path' => $path
            ], (array) a(Config::get($key, [])), $lot);
            // Set `time` value from the page’s file name
            if (
                $n &&
                is_numeric($n[0]) &&
                (
                    // `2017-04-21.page`
                    substr_count($n, '-') === 2 ||
                    // `2017-04-21-14-25-00.page`
                    substr_count($n, '-') === 5
                ) &&
                is_numeric(str_replace('-', "", $n)) &&
                preg_match('#^\d{4,}(?:-\d{2}){2}(?:(?:-\d{2}){3})?$#', $n)
            ) {
                $t = new Date($n);
                $this->lot['time'] = $t->format(DATE_WISE);
                $this->lot['title'] = $t->F2;
            // Else, set `time` value from the page’s `time.data` if any
            } else if ($t = File::open(Path::F($path) . DS . 'time.data')->read()) {
                $this->lot['time'] = (new Date($t))->format(DATE_WISE);
            }
            // Static `update` value from the page’s `update.data` if any
            if ($t = File::open(Path::F($path) . DS . 'update.data')->read()) {
                $this->lot['update'] = (new Date($t))->format(DATE_WISE);
            }
            self::$page[$id] = $this->lot;
        }
        parent::__construct();
    }

    public function __call($key, $lot = []) {
        if (self::_($key)) {
            return parent::__call($key, $lot);
        }
        // Example: `$page->__call('foo.bar')`
        if (strpos($key, '.') !== false) {
            list($key, $keys) = explode('.', $key, 2);
        } else {
            $keys = null;
        }
        $a = $this->lot;
        $extern = isset($a['path']) ? Path::F($a['path']) . DS . str_replace('_', '-', $key) . '.data' : null;
        if ($extern && is_file($a['path'])) {
            // Prioritize data from a file…
            if ($data = File::open($extern)->get()) {
                $extern = null; // Stop!
                $a[$key] = e($data);
            } else if ($datas = glob(substr_replace($extern, '.*.data', -5, 5), GLOB_NOSORT)) {
                $extern = null; // Stop!
                foreach ($datas as $v) {
                    Anemon::set($a, str_replace('-', '_', Path::N($v)), e(file_get_contents($v)));
                }
            } else if ($page = file_get_contents($a['path'])) {
                $a = array_replace($a, e(self::apart($page), ['$', 'content']));
            }
        }
        if (!array_key_exists($key, $a)) {
            $a[$key] = null;
        }
        // Prioritize data from a file…
        if ($extern) {
            if ($data = File::open($extern)->get()) {
                $a[$key] = e($data);
            } else if ($datas = glob(substr_replace($extern, '.*.data', -5, 5), GLOB_NOSORT)) {
                foreach ($datas as $v) {
                    Anemon::set($a, str_replace('-', '_', Path::N($v)), e(file_get_contents($v)));
                }
            }
        }
        self::$page[$this->hash] = $this->lot = $a;
        $fail = isset($lot[0]) ? $lot[0] : null;
        if ($fail === false) {
            // Disable hook(s) with `$page->key(false)`
            return isset($keys) ? Anemon::get($a[$key], $keys, null) : $a[$key];
        } else {
            if ($fail instanceof \Closure) {
                // As function call with `$page->key(function($text) { … })`
                $a[$key] = $fail($a[$key], $this);
            } else if ($a[$key] === null) {
                // Other(s)… `$page->key('default value')`
                $a[$key] = $fail;
            }
        }
        if ($this->NS === false) {
            // Disable hook(s) with `$page = new Page('path\to\file.page', [], false)`
            return isset($keys) ? Anemon::get($a[$key], $keys, $fail) : $a[$key];
        } else if (is_array($this->NS)) {
            $name = [];
            foreach ($this->NS as $v) {
                $name[] = $v . '.' . $key;
            }
        } else {
            $name = $this->NS . '.' . $key;
        }
        $v = Hook::fire($name, [isset($keys) ? Anemon::get($a[$key], $keys, $fail) : $a[$key], $a, $this, $key]);
        if (count($lot) && $x = __is_instance__($v)) {
            if (method_exists($x, '__invoke')) {
                $v = call_user_func([$x, '__invoke'], ...$lot);
            }
        }
        return $v;
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = self::$page[$this->hash][$key] = $value;
    }

    public function __get($key) {
        return $this->__call($key);
    }

    // Fix case for `isset($page->key)` or `!empty($page->key)`
    public function __isset($key) {
        return !!$this->__call($key);
    }

    public function __unset($key) {
        $this->__set($key, null);
    }

    public function __toString() {
        if ($s = $this->__call('$')) {
            return $s;
        }
        $path = isset($this->lot['path']) ? $this->lot['path'] : null;
        return $path && file_exists($path) ? file_get_contents($path) : "";
    }

    public static function apart($input, $key = null, $fail = null, $eval = false) {
        // Get specific property…
        if ($key === 'content') {
            $input = explode("\n...", n(is_file($input) ? file_get_contents($input) : $input), 2);
            return trim(isset($input[1]) ? $input[1] : $input[0], "\n");
        } else if (isset($key)) {
            // By path… (faster)
            if (is_file($input)) {
                if ($o = fopen($input, 'r')) {
                    $output = $fail;
                    while (($s = fgets($o, 1024)) !== false) {
                        $s = trim($s);
                        if ($s === '...') {
                            break; // Page header end!
                        }
                        if (strpos($s, $key . ': ') === 0) {
                            $s = explode(': ', $s, 2);
                            $output = isset($s[1]) ? trim($s[1]) : $fail;
                            break;
                        }
                    }
                    fclose($o);
                    return $eval ? e($output, is_array($eval) ? $eval : []) : $output;
                }
                return $fail;
            }
            // By content…
            $input = n($input);
            $s = strpos($input, "\n...");
            $ss = strpos($input, $k = "\n" . $key . ': ');
            if ($s !== false && $ss !== false && $ss < $s) {
                $input = substr($input, $ss + strlen($k)) . "\n";
                $output = trim(substr($input, 0, strpos($input, "\n")));
                return $eval ? e($output, is_array($eval) ? $eval : []) : $output;
            }
            return $fail;
        }
        // Get all propert(y|ies) embedded…
        $data = [];
        $input = n($input);
        if (strpos($input, "---\n") !== 0) {
            $data['content'] = $input;
        } else {
            $input = str_replace([X . "---\n", X], "", X . $input . "\n\n");
            $input = explode("\n...\n\n", $input, 2);
            // Do data…
            $data = From::YAML($input[0], '  ', [], false);
            $data = $eval ? e($data, is_array($eval) ? $eval : []) : $data;
            // Do content…
            if (!isset($data['content'])) {
                $data['content'] = trim(isset($input[1]) ? $input[1] : "", "\n");
            }
        }
        return $data;
    }

    public static function unite($input) {
        $content = "";
        if (isset($input['content'])) {
            $content = $input['content'];
            unset($input['content']);
        }
        $header = To::YAML($input);
        return ($header ? "---\n" . $header . "\n..." : "") . ($content ? "\n\n" . $content : "");
    }

    private static $data = [];

    public static function open($path, $lot = [], $NS = []) {
        self::$data = [['path' => $path], $lot, $NS];
        return new static($path, $lot, $NS);
    }

    public static function set($input, $fn = null) {
        if (!is_array($input)) {
            if (is_callable($fn)) {
                self::$data[0][$input] = call_user_func($fn, ...self::$data);
                $input = [];
            } else {
                $input = ['content' => $input];
            }
        }
        self::$data[0] = array_replace(self::$data[0], $input);
        foreach (self::$data[0] as $k => $v) {
            if ($v === false) unset(self::$data[0][$k]);
        }
        return new static(...self::$data);
    }

    public function get($key, $fail = null) {
        if (is_array($key)) {
            $output = [];
            foreach ($key as $k => $v) {
                $output[$k] = $this->__call($k, [$v]);
            }
            return $output;
        }
        return $this->__call($key, [$fail]);
    }

    public function saveTo($path, $consent = 0600) {
        $data = self::$data[0];
        unset($data['path']);
        File::set(self::unite($data))->saveTo($path, $consent);
    }

    public function save($consent = 0600) {
        return self::saveTo(self::$data[0]['path'], $consent);
    }

}