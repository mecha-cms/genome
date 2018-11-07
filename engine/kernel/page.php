<?php

class Page extends Genome {

    public $path = null;
    public $lot = [];

    private $NS = "";
    private $hash = "";

    private static $page = []; // Cache!

    public function __construct($path = null, array $lot = [], $NS = []) {
        $key = c2f(static::class, '_', '/');
        $this->path = $path;
        $this->NS = is_array($NS) ? extend(['*', $key], $NS, false) : $NS;
        $this->hash = $id = json_encode([$path, $lot, $this->NS]);
        if (isset(self::$page[$id])) {
            $this->lot = self::$page[$id];
        } else {
            $n = $path ? Path::N($path) : null;
            $x = Path::X((string) $path, "");
            $c = $m = $_SERVER['REQUEST_TIME'] ?? time();
            if (file_exists($path)) {
                $c = filectime($path); // File creation time
                $m = filemtime($path); // File modification time
            }
            $this->lot = extend([
                'time' => $c,
                'update' => $m,
                'slug' => $n,
                'title' => $n !== null ? To::title((string) $n) : null, // Fake `title` data from the page’s file name
                'state' => $x,
                'type' => u($x), // Fake `type` data from the page’s file extension
                'id' => sprintf('%u', $c),
                'path' => $path,
                'url' => $path !== null ? To::URL((string) $path) : null
            ], (array) Config::get($key, [], true), $lot, false);
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
                $this->lot['title'] = $t->format(strtr(DATE_WISE, '-', '/'));
            // Else, set `time` value from the page’s `time.data` if any
            } else if ($t = File::open(Path::F((string) $path) . DS . 'time.data')->get()) {
                $this->lot['time'] = $t;
            }
            // Static `update` value from the page’s `update.data` if any
            if ($t = File::open(Path::F((string) $path) . DS . 'update.data')->get()) {
                $this->lot['update'] = $t;
            }
            $this->lot['time'] = new Date($this->lot['time']);
            $this->lot['update'] = new Date($this->lot['update']);
            self::$page[$id] = $this->lot;
        }
        parent::__construct();
    }

    public function __call(string $key, array $lot = []) {
        if (self::_($key) || $key === 'set') { // @see `function _set_()`
            return parent::__call($key, $lot);
        }
        // Example: `$page->__call('foo.bar')`
        $keys = null;
        if (strpos($key, '.') !== false) {
            list($key, $keys) = explode('.', $key, 2);
        }
        $a = $this->lot;
        $path = $this->path;
        $extern = $path ? Path::F($path) . DS . strtr($key, '_', '-') . '.data' : null;
        if ($extern && is_file($path)) {
            // Prioritize data from a file…
            if ($data = File::open($extern)->get()) {
                $extern = null; // Stop!
                $a[$key] = e($data);
            } else if ($page = file_get_contents($path)) {
                $a = extend($a, self::apart($page, null, null, ['$', 'content']), false);
            }
        }
        if (!array_key_exists($key, $a)) {
            $a[$key] = null;
        }
        // Prioritize data from a file…
        if ($extern && $data = File::open($extern)->get()) {
            $a[$key] = e($data);
        }
        $this->lot = self::$page[$this->hash] = $a;
        $test = $lot[0] ?? null;
        if ($test === false) {
            // Disable hook(s) with `$page->foo(false)`
            return isset($keys) ? Anemon::get($a[$key], $keys, null) : $a[$key];
        } else {
            if ($test instanceof \Closure) {
                // As function call with `$page->foo(function($text) { … })`
                $a[$key] = fn($test, [$a[$key]], $this);
            }
        }
        if ($this->NS === false) {
            // Disable hook(s) with `$page = new Page('.\path\to\file.page', [], false)`
            return isset($keys) ? Anemon::get($a[$key], $keys, $fail) : $a[$key];
        } else if (is_array($this->NS)) {
            $name = [];
            foreach ($this->NS as $v) {
                $name[] = $v . '.' . $key;
            }
        } else {
            $name = $this->NS . '.' . $key;
        }
        $v = Hook::fire($name, [isset($keys) ? Anemon::get($a[$key], $keys, $fail) : $a[$key], $lot], $this);
        if (count($lot) && $x = fn\is\instance($v)) {
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
        if ($str = $this->__call('$')) {
            return $str;
        }
        $path = $this->path;
        return $path && file_exists($path) ? file_get_contents($path) : "";
    }

    public static function apart($in, $key = null, $fail = null, $eval = false) {
        // Get specific property…
        if ($key === 'content') {
            $in = explode("\n...", n(Is::file($in) ? file_get_contents($in) : $in), 2);
            return trim($in[1] ?? $in[0], "\n");
        } else if (isset($key)) {
            // By path… (faster)
            if (Is::file($in)) {
                if ($o = fopen($in, 'r')) {
                    $out = $fail;
                    while (($s = fgets($o, 1024)) !== false) {
                        $s = trim($s);
                        if ($s === '...') {
                            break; // Page header end!
                        }
                        if (strpos($s, $key . ': ') === 0) {
                            $s = explode(': ', $s, 2);
                            $out = isset($s[1]) ? trim($s[1]) : $fail;
                            break;
                        }
                    }
                    fclose($o);
                    return $eval ? e($out, is_array($eval) ? $eval : []) : $out;
                }
                return $fail;
            }
            // By content…
            $in = n($in);
            $s = strpos($in, "\n...");
            $ss = strpos($in, $k = "\n" . $key . ': ');
            if ($s !== false && $ss !== false && $ss < $s) {
                $in = substr($in, $ss + strlen($k)) . "\n";
                $out = trim(substr($in, 0, strpos($in, "\n")));
                return $eval ? e($out, is_array($eval) ? $eval : []) : $out;
            }
            return $fail;
        }
        // Get all propert(y|ies) embedded…
        $data = [];
        $in = n($in);
        if (strpos($in, "---\n") !== 0) {
            $data['content'] = $in;
        } else {
            $in = str_replace([X . "---\n", X], "", X . $in . "\n\n");
            $in = explode("\n...\n\n", $in, 2);
            // Do data…
            $data = From::YAML($in[0], '  ', [], false);
            $data = $eval ? e($data, is_array($eval) ? $eval : []) : $data;
            // Do content…
            if (!isset($data['content'])) {
                $data['content'] = trim(isset($in[1]) ? $in[1] : "", "\n");
            }
        }
        return $data;
    }

    public static function unite(array $in = []) {
        $content = "";
        if (isset($in['content'])) {
            $content = $in['content'];
            unset($in['content']);
        }
        $header = To::YAML($in);
        return ($header ? "---\n" . $header . "\n..." : "") . ($content ? "\n\n" . $content : "");
    }

    public static function open($path = null, array $lot = [], $NS = []) {
        return new static($path, $lot, $NS);
    }

    protected function _set_($in, $fn = null) {
        $path = $this->path;
        $data = is_file($path) ? self::apart(file_get_contents($path)) : [];
        $this->lot = extend($data, ['path' => $path], false);
        if (!is_array($in)) {
            if (is_callable($fn)) {
                $this->lot[$in] = call_user_func($fn, ...$this->lot);
                $in = [];
            } else {
                $in = ['content' => $in];
            }
        }
        $this->lot = extend($this->lot, $in);
        foreach ($this->lot as $k => $v) {
            if ($v === false) unset($this->lot[$k]);
        }
        return $this;
    }

    public function get($key, $fail = null) {
        if (is_array($key)) {
            $out = [];
            foreach ($key as $k => $v) {
                $out[$k] = $this->__call($k, [$v]);
            }
            return $out;
        }
        return $this->__call($key, [$fail]);
    }

    public function has($key) {
        $data = Path::F($this->path) . DS . $key . '.data';
        return file_exists($data) ? filesize($data) > 0 : self::apart($this->path, $key) !== null;
    }

    public function saveTo(string $path, $consent = 0600) {
        unset($this->lot['path']);
        File::set(self::unite($this->lot))->saveTo($path, $consent);
    }

    public function save($consent = 0600) {
        return $this->saveTo($this->lot['path'], $consent);
    }

}