<?php

class Page extends Genome {

    protected $lot = [];
    protected $lot_c = [];

    private $NS = "";
    private $hash = "";
    private static $page = []; // Cache!

    public function __construct($input = null, $lot = [], $NS = []) {
        $this->NS = is_array($NS) ? array_replace(['*', __c2f__(static::class, '_', '\\')], $NS) : $NS;
        $path = is_array($input) ? (isset($input['path']) ? $input['path'] : null) : $input;
        $id = $this->hash = md5(serialize($NS) . $path);
        if (isset(self::$page[$id])) {
            $this->lot = self::$page[$id][1];
            $this->lot_c = self::$page[$id][0];
        } else {
            $t = File::T($path, time());
            $date = date(DATE_WISE, $t);
            $n = $path ? Path::N($path) : null;
            $x = Path::X($path);
            $this->lot_c = array_replace([
                'time' => $date,
                'update' => $date,
                'slug' => $n,
                'title' => To::title($n), // Fake `title` data from the page’s file name
                'type' => u($x), // Fake `type` data from the page’s file extension
                'state' => $x,
                'id' => (string) $t,
                'url' => To::url($path)
            ], (array) a(Config::get('page', [])), $lot);
            $this->lot = is_array($input) ? $input : (isset($input) ? ['path' => $input] : []);
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
                $this->lot['time'] = $t->format();
                $this->lot['title'] = $t->F2;
                $this->lot['id'] = $t->format('U');
            // else, set `time` value by page’s file modification time
            } else {
                $t = new Date(File::open(Path::F($path) . DS . 'time.data')->read($date));
                $this->lot['time'] = $t->format();
                $this->lot['id'] = $t->format('U');
            }
            if (!array_key_exists('date', $this->lot)) {
                $this->lot['date'] = new Date($this->lot['time']);
            }
            self::$page[$id] = [$this->lot_c, $this->lot];
        }
        parent::__construct();
    }

    public function __call($key, $lot = []) {
        if (self::_($key)) {
            return parent::__call($key, $lot);
        }
        $a = $this->lot;
        $a_c = $this->lot_c;
        if (!array_key_exists($key, $a)) {
            if (isset($a['path']) && is_file($a['path'])) {
                // Prioritize data from a file…
                if ($data = File::open(Path::F($a['path']) . DS . $key . '.data')->get()) {
                    $a[$key] = e($data);
                } else if ($page = file_get_contents($a['path'])) {
                    $a = array_replace($a_c, $a, e(self::apart($page), 'content'));
                }
            }
            if (!array_key_exists($key, $a)) {
                $a[$key] = array_key_exists($key, $a_c) ? e($a_c[$key]) : null;
            }
        }
        // Prioritize data from a file…
        if (isset($a['path']) && $data = File::open(Path::F($a['path']) . DS . $key . '.data')->get()) {
            $a[$key] = e($data);
        }
        $this->lot = $a;
        self::$page[$this->hash][1] = $a;
        if (isset($lot[0])) {
            if ($lot[0] === false) {
                return $a[$key]; // Disable hook(s) with `$page->key(false)`
            } else {
                if ($lot[0] instanceof \Closure) {
                    // As function call with `$page->key(function($text) { … })`
                    $a[$key] = call_user_func($lot[0], $a[$key], $this);
                } else if ($a[$key] === null) {
                    // Other(s)… `$page->key('default value')`
                    $a[$key] = $lot[0];
                }
            }
        }
        if ($this->NS === false) {
            return $a[$key]; // Disable hook(s) with `$page = new Page('path\to\file.page', [], false)`
        } else if (is_array($this->NS)) {
            $name = [];
            foreach ($this->NS as $v) {
                $name[] = $v . '.' . $key;
            }
        } else {
            $name = $this->NS . '.' . $key;
        }
        return Hook::fire($name, [$a[$key], $a, $this, $key]);
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = self::$page[$this->hash][1][$key] = $value;
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
                    return $eval ? e($output) : $output;
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
                return $eval ? e($output) : $output;
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
            $data = From::yaml($input[0], '  ', [], $eval);
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
        $header = To::yaml($input);
        return ($header ? "---\n" . $header . "\n..." : "") . ($content ? "\n\n" . $content : "");
    }

    private static $data = [];

    public static function open($path, $lot = [], $NS = []) {
        self::$data = ['path' => $path];
        return new static($path, $lot, $NS);
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
        return new static(isset(self::$data['path']) ? self::$data['path'] : null, $input);
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
        $data = self::$data;
        unset($data['path']);
        File::write(self::unite($data))->saveTo($path, $consent);
    }

    public function save($consent = 0600) {
        return self::saveTo(self::$data['path'], $consent);
    }

}