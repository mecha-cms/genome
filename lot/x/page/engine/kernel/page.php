<?php

class Page extends File {

    private $read;

    protected static $page;

    protected $id;
    protected $lot;
    protected $prefix;

    protected function any(string $kin, array $lot = []) {
        if (isset(self::$page[$id = $this->id][$kin]) && !empty($this->read[$kin])) {
            $v = self::$page[$id][$kin]; // Load from cache…
        } else {
            $v = $this->offsetGet($kin);
            // Set first, to be used in the hook scope…
            $this->lot[$kin] = self::$page[$id][$kin] = $v;
            // Do the hook once!
            $v = Hook::fire(map($this->prefix, function($v) use($kin) {
                return $v .= '.' . $kin;
            }), [$v, $lot], $this);
            $this->read[$kin] = 2; // Done hook
            if ($lot && is_callable($v) && !is_string($v)) {
                $v = call_user_func($v, ...$lot);
            }
            // Set again to be used later…
            $this->lot[$kin] = self::$page[$id][$kin] = $v;
        }
        return $v;
    }

    public function __call(string $kin, array $lot = []) {
        if (parent::_($kin = p2f($kin))) {
            return parent::__call($kin, $lot);
        }
        return $this->any($kin, $lot);
    }

    public function __construct(string $path = null, array $lot = []) {
        parent::__construct($path);
        $c = strtr(c2f(static::class), ['.' => "\\."]); // Current class name
        $cc = strtr(c2f(self::class), ['.' => "\\."]); // This very class name
        $prefix = array_unique([$cc, $c]);
        $id = json_encode([$path, $lot, $prefix]);
        $this->id = $id;
        $this->prefix = $prefix;
        // Set pre-defined page property
        if (!isset(self::$page[$id])) {
            self::$page[$id] = array_replace_recursive((array) State::get('x.' . $cc, true), (array) State::get('x.' . $c, true), $lot);
        }
        $this->lot = self::$page[$id] ?? [];
    }

    // Inherit to `File::__get()`
    public function __get(string $key) {
        return parent::__get($key) ?? $this->__call($key);
    }

    public function __set(string $key, $value) {
        $this->offsetSet(p2f($key), $value);
    }

    // Inherit to `File::__toString()`
    public function __toString() {
        return To::page($this->lot);
    }

    public function ID(...$lot) {
        return $this->any('id', $lot) ?? (($t = $this->time()->format('U')) ? sprintf('%u', $t) : null);
    }

    // Inherit to `File::URL()`
    public function URL(...$lot) {
        if ($this->exist) {
            return $this->any('url', $lot) ?? trim($GLOBALS['url'] . '/' . Path::R(Path::F($this->path), LOT . DS . 'page', '/'), '/');
        }
        return null;
    }

    // Inherit to `File::get()`
    public function get($key = null) {
        if (is_array($key)) {
            $out = [];
            foreach ($key as $k => $v) {
                // `$page->get(['foo.bar' => 0])`
                if (strpos($k, '.') !== false) {
                    $kk = explode('.', $k, 2);
                    if (is_array($vv = $this->any($kk[0]))) {
                        $out[$k] = get($vv, $kk[1]) ?? $v;
                        continue;
                    }
                }
                $out[$k] = $this->any($k) ?? $v;
            }
            return $out;
        }
        // `$page->get('foo.bar')`
        if (strpos($key, '.') !== false) {
            $k = explode('.', $key, 2);
            if (is_array($v = $this->any($k[0]))) {
                return get($v, $k[1]);
            }
        }
        return $this->any($key);
    }

    // Inherit to `File::getIterator()`
    public function getIterator() {
        if ($this->exist) {
            $out = From::page(file_get_contents($path = $this->path), null, true);
            foreach (g(Path::F($path), 'data') as $k => $v) {
                $out[basename($k, '.data')] = e(file_get_contents($k));
            }
        } else {
            $out = [];
        }
        return new \ArrayIterator($out);
    }

    // Inherit to `File::jsonSerialize()`
    public function jsonSerialize() {
        return $this->exist ? From::page(file_get_contents($this->path), null, true) : [];
    }

    // Inherit to `File::offsetGet()`
    public function offsetGet($i) {
        // Read once!
        if ($this->exist && empty($this->read[$i])) {
            $id = $this->id;
            // Prioritize data from a file…
            if (is_file($f = Path::F($this->path) . DS . $i . '.data')) {
                $this->read[$i] = 1; // Done read
                unset(self::$page[$id][$i]); // Remove cached value if any
                return ($this->lot[$i] = a(e(file_get_contents($f))));
            }
            $any = From::page(file_get_contents($this->path), null, true);
            foreach ($any as $k => $v) {
                $this->read[$k] = 1; // Done read
                unset(self::$page[$id][$k]); // Remove cached value if any
            }
            $this->lot = array_replace_recursive($this->lot, $any);
        }
        return $this->lot[$i] ?? null;
    }

    // Inherit to `File::offsetSet()`
    public function offsetSet($i, $value) {
        $id = $this->id;
        if (isset($i)) {
            self::$page[$id][$i] = $this->lot[$i] = $value;
        } else {
            self::$page[$id][] = $this->lot[] = $value;
        }
    }

    // Inherit to `File::offsetUnset()`
    public function offsetUnset($i) {
        unset(self::$page[$this->id][$i], $this->lot[$i]);
    }

    public function save($seal = null) {
        $data = $this->lot;
        $id = $this->id;
        foreach ($data as $k => $v) {
            if (isset(self::$page[$id][$k])) {
                unset($data[$k]);
            }
        }
        $this->value[0] = To::page($data);
        return parent::save($seal);
    }

    // Inherit to `File::set()`
    public function set($key, $value = null) {
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

    // Inherit to `File::time()`
    public function time(string $format = null) {
        $n = parent::name();
        // Set `time` value from the page’s file name
        if (
            is_string($n) && (
                // `2017-04-21.page`
                substr_count($n, '-') === 2 ||
                // `2017-04-21-14-25-00.page`
                substr_count($n, '-') === 5
            ) &&
            is_numeric(str_replace('-', "", $n)) &&
            preg_match('/^[1-9]\d{3,}-(0\d|1[0-2])-(0\d|[1-2]\d|3[0-1])(-([0-1]\d|2[0-4])(-([0-5]\d|60)){2})?$/', $n)
        ) {
            $date = new Date($n);
        // Else…
        } else {
            $date = new Date($this->offsetGet('time') ?? parent::time());
        }
        return $format ? $date($format) : $date;
    }

    // Inherit to `File::type()`
    public function type(...$lot) {
        return $this->any('type', $lot) ?? 'text/html';
    }

    // Inherit to `File::update()`
    public function update(string $format = null) {
        $date = new Date($this->offsetGet('update') ?? parent::update());
        return $format ? $date($format) : $date;
    }

}