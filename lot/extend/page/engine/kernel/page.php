<?php

class Page extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable, \Serializable {

    private $read;

    protected static $page;

    protected $id;
    protected $lot;
    protected $prefix;

    public $exist;
    public $f;
    public $path;

    // Set pre-defined page property
    public static $data = [];

    protected function _set_($key, $value = null) {
        $id = $this->id ?? "";
        if (!$this->exist) {
            $this->lot = self::$page[$id] = [];
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if ($v === false) {
                    unset($this->lot[$k], self::$page[$id][$k]);
                    continue;
                }
                $this->lot[$k] = $v;
            }
        } else if (isset($value)) {
            $this->lot[$key] = $value;
        } else {
            // `$page->set('<p>abcdef</p>')`
            $this->lot['content'] = $key;
        }
        return $this;
    }

    public function __call(string $key, array $lot = []) {
        // @see `function _set_()`
        if ($key === 'set' || self::_($key)) {
            return parent::__call($key, $lot);
        }
        $key = p2f($key);
        if (isset(self::$page[$id = $this->id][$key])) {
            $v = self::$page[$id][$key]; // Load from cache…
        } else {
            $v = $this->offsetGet($key);
            // Set…
            $this->lot[$key] = self::$page[$id][$key] = $v;
            // Do the hook once!
            $v = Hook::fire(map($this->prefix, function($v) use($key) {
                return $v .= '.' . $key;
            }), [$v, $lot], $this);
            if ($lot && is_callable($v) && !is_string($v)) {
                $v = call_user_func($v, ...$lot);
            }
            // Set…
            $this->lot[$key] = self::$page[$id][$key] = $v;
        }
        return $v;
    }

    public function __construct(string $path = null, array $lot = [], array $prefix = []) {
        parent::__construct();
        $c = c2f(static::class, '_', '/');
        $prefix = array_replace(['*', $c], $prefix);
        $id = json_encode([$path, $lot, $prefix]);
        $this->exist = $f = is_file($path);
        $this->f = $file = new File($path);
        $this->id = $id;
        $this->path = $f ? $path : null;
        $this->prefix = $prefix;
        // Set pre-defined page property
        $this->lot = array_replace_recursive([
            'id' => $f ? sprintf('%u', (string) $file->time) : null,
            'name' => $n = $file->name,
            'path' => $path,
            'slug' => $n,
            'time' => $file->time(DATE_FORMAT),
            'title' => $f ? To::title($n) : null,
            'update' => $file->update(DATE_FORMAT),
            'url' => $file->URL,
            'x' => $file->x
        ], (array) static::$data, $lot);
    }

    public function __get(string $key) {
        if (method_exists($this, $key)) {
            if ((new \ReflectionMethod($this, $key))->isPublic()) {
                return $this->{$key}();
            }
        }
        return $this->__call($key);
    }

    public function __toString() {
        if (is_string($v = $this->offsetGet('$'))) {
            return $v;
        }
        $path = $this->path;
        return $path ? file_get_contents($path) : "";
    }

    public function count() {
        return $this->exist ? 1 : 0;
    }

    public function get($key) {
        if (is_array($key)) {
            $out = [];
            foreach ($key as $k => $v) {
                // `$page->get(['foo.bar' => 0])`
                if (strpos($k, '.') !== false) {
                    $kk = explode('.', $k, 2);
                    if (is_array($vv = $this->__call($kk[0]))) {
                        $out[$k] = get($vv, $kk[1]) ?? $v;
                        continue;
                    }
                }
                $out[$k] = $this->__call($k) ?? $v;
            }
            return $out;
        }
        // `$page->get('foo.bar')`
        if (strpos($key, '.') !== false) {
            $k = explode('.', $key, 2);
            if (is_array($v = $this->__call($k[0]))) {
                return get($v, $k[1]);
            }
        }
        return $this->__call($key);
    }

    public function getIterator() {
        return new \ArrayIterator($this->lot);
    }

    public function jsonSerialize() {
        return $this->lot;
    }

    public function offsetExists($i) {
        return isset($this->lot[$i]);
    }

    public function offsetGet($i) {
        if ($this->exist && empty($this->read[$i])) {
            // Prioritize data from a file…
            if (is_file($f = Path::F($this->path) . DS . $i . '.data')) {
                // Read once!
                $this->read[$i] = 1;
                return ($this->lot[$i] = a(e(file_get_contents($f))));
            }
            $any = self::apart(file_get_contents($this->path), null, true);
            foreach ($any as $k => $v) {
                // Read once!
                $this->read[$k] = 1;
            }
            $this->lot = array_replace_recursive($this->lot, $any);
        }
        return $this->lot[$i] ?? null;
    }

    public function offsetSet($i, $value) {
        if (isset($i)) {
            $this->lot[$i] = $value;
        } else {
            $this->lot[] = $value;
        }
    }

    public function offsetUnset($i) {
        unset($this->lot[$i]);
    }

    public function save() {
        return $this->saveTo($this->path, 0600);
    }

    public function saveAs(string $name) {
        if ($this->exist) {
            return $this->saveTo(dirname($this->path) . DS . basename($name), 0600);
        }
        return false;
    }

    public function saveTo(string $path) {
        unset($this->lot['path']);
        return File::put(self::unite($this->lot))->saveTo($path, 0600);
    }

    public function serialize() {
        if ($this->exist) {
            return serialize(self::apart(file_get_contents($this->path)));
        }
        return serialize([]);
    }

    public function unserialize($v) {
        $page = new static;
        $page->lot = unserialize($v);
    }

    public static function apart(string $in, $key = null, $eval = false) {
        if (strpos($in = n($in), "---\n") !== 0) {
            // Add empty header
            $in = "---\n...\n\n" . $in;
        }
        $v = From::YAML($in, '  ', true, $eval);
        $v = $v[0] + ['content' => $v["\t"] ?? ""];
        return isset($key) ? (array_key_exists($key, $v) ? $v[$key] : null) : $v;
    }

    public static function open(string $path, array $lot = [], array $prefix = []) {
        return new static($path, $lot, $prefix);
    }

    public static function unite(array $lot) {
        $content = $lot['content'] ?? "";
        unset($lot['content']);
        $lot = [
            0 => $lot,
            "\t" => $content
        ];
        return To::YAML($lot, '  ', true);
    }

}