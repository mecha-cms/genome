<?php

class Elevator extends Genome {

    const HUB = '&#x25C6;';
    const NORTH = '&#x25B2;';
    const SOUTH = '&#x25BC;';
    const WEST = '&#x25C0;';
    const EAST = '&#x25B6;';

    public $config = [];
    public $c = [];

    protected $bucket = [];
    protected $NS = "";

    protected function unite($input, $alt = ['span']) {
        if (!$alt || !$input) return "";
        $input = array_replace_recursive($alt, $input);
        return HTML::unite($input);
    }

    public function __construct($input = [], $chunk = [5, 0], $path = true, $config = []) {
        $key = c2f(static::class, '_', '/');
        $this->c = [
            // <: previous
            // =: parent or current
            // >: next
            'direction' => [
                '<' => 'up',
                '=' => 'hub',
                '>' => 'down'
            ],
            'union' => [
                '!' => [ // not active
                    0 => 'span',
                    2 => ['href' => null]
                ],
                '<' => [
                    0 => 'a',
                    1 => self::NORTH
                ],
                '=' => [
                    0 => 'a',
                    1 => self::HUB
                ],
                '>' => [
                    0 => 'a',
                    1 => self::SOUTH
                ]
            ],
            'lot' => [$input, $chunk, $path, $config]
        ];
        $c = array_replace_recursive($this->c, $config);
        $d = $c['direction'];
        $p = $GLOBALS['URL']['path'];
        $q = str_replace('&', '&amp;', $GLOBALS['URL']['query']);
        $r = $GLOBALS['URL']['current'];
        if (is_array($chunk)) {
            $chunk = array_replace([5, 0], $chunk);
            $input = Anemon::eat($input)->chunk($chunk[0]);
        }
        if ($path === true) {
            $path = $r;
        }
        $path = rtrim($path, '/');
        $parent = Path::D($path);
        // @pages
        if (is_array($chunk)) {
            $i = $chunk[1];
            if ($d['<'] !== false)
                $this->bucket[$d['<']] = isset($input[$i - 1]) ? $path . '/' . $i . $q : null;
            if ($d['='] !== false)
                $this->bucket[$d['=']] = $p !== "" ? ($path !== $r ? $path : $parent) . $q : null;
            if ($d['>'] !== false)
                $this->bucket[$d['>']] = isset($input[$i + 1]) ? $path . '/' . ($i + 2) . $q : null;
        // @page
        } else {
            $i = ($input ? array_search($chunk, $input) : 0) ?: 0;
            if ($d['<'] !== false)
                $this->bucket[$d['<']] = isset($input[$i - 1]) ? $path . '/' . $input[$i - 1] . $q : null;
            if ($d['='] !== false)
                $this->bucket[$d['=']] = $p !== "" ? ($path !== $r ? $path : $parent) . $q : null;
            if ($d['>'] !== false)
                $this->bucket[$d['>']] = isset($input[$i + 1]) ? $path . '/' . $input[$i + 1] . $q : null;
        }
        $this->config = $c;
        $this->NS = $key;
        parent::__construct();
    }

    public function __get($key) {
        return array_key_exists($key, $this->bucket) ? $this->bucket[$key] : null;
    }

    // Fix case for `isset($elevator->key)` or `!empty($elevator->key)`
    public function __isset($key) {
        return !!$this->__get($key);
    }

    public function __unset($key) {
        unset($this->bucket[$key]);
    }

    public function __call($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $text = array_shift($lot);
        $u = $this->config['union'];
        $d = array_search($kin, $this->config['direction']);
        if ($d !== false && ($text || $text === "")) {
            if ($text !== true) $u[$d][1] = $text;
            return isset($this->bucket[$kin]) ? $this->unite(array_replace_recursive($u[$d], [2 => ['href' => $this->bucket[$kin]]])) : $this->unite($u['!'], $u[$d]);
        }
        return $this->bucket[$kin] ?? $text;
    }

    public function __toString() {
        global $language;
        $c = $this->config;
        $d = $c['direction'];
        $u = $c['union'];
        $html = [];
        if ($d['<'] !== false)
            $html[] = $this->{$d['<']}(true);
        if ($d['='] !== false)
            $html[] = $this->{$d['=']}(true);
        if ($d['>'] !== false)
            $html[] = $this->{$d['>']}(true);
        return Hook::fire($this->NS . '.yield', [implode(' ', $html), $this]);
    }

}