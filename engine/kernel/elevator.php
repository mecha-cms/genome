<?php

class Elevator extends Genome {

    public $config = [
        // `-1`: previous
        //  `0`: current
        //  `1`: next
        'direction' => [
            '-1' => 'up',
            '0' => "",
            '1' => 'down'
        ],
        'union' => [
            '-2' => [ // not active
                0 => 'span',
                2 => ['href' => null]
            ],
            '-1' => [
                0 => 'a',
                1 => '&#x25B2;'
            ],
            '0' => [
                0 => 'a',
                1 => '&#x25C6;'
            ],
            '1' => [
                0 => 'a',
                1 => '&#x25BC;'
            ]
        ]
    ];

    protected $bucket = [];
    protected $NS = "";

    public function __construct($input, $chunk = 5, $index = 0, $path = true, $config = [], $NS = "") {
        $c = Hook::NS(strtolower(static::class) . Anemon::NS . 'union', Anemon::extend($this->config, $config), $this->config);
        $d = $c['direction'];
        global $url;
        $input = Anemon::eat($input)->chunk($chunk);
        if ($path === true) {
            $path = $url->current;
        }
        $path = rtrim($path, '/');
        $this->bucket = [
            $d['-1'] => !empty($input[$index - 1]) ? $path . '/' . $index : null,
            $d['0'] => $path !== $url->current ? $path : null,
            $d['1'] => !empty($input[$index + 1]) ? $path . '/' . ($index + 2) : null
        ];
        $this->config = $c;
        $this->NS = $NS ? Anemon::NS . $NS : "";
    }

    protected function _unite($input, $alt = ['span']) {
        if (!$alt || !$input) return "";
        $input = array_replace_recursive($alt, $input);
        return call_user_func_array('HTML::unite', $input);
    }

    public function __get($key) {
        return array_key_exists($key, $this->bucket) ? $this->bucket[$key] : false;
    }

    public function __call($kin, $lot) {
        $text = array_shift($lot);
        $u = $this->config['union'];
        $d = array_flip($this->config['direction'])[$kin];
        if ($text || $text === "") {
            if ($text !== true) $u[$d][1] = $text;
            return isset($this->bucket[$kin]) ? $this->_unite(array_replace_recursive($u[$d], [2 => ['href' => $this->bucket[$kin]]])) : $this->_unite($u['-2'], $u[$d]);
        }
        return isset($this->bucket[$kin]) ? $this->bucket[$kin] : $text;
    }

    public function __toString() {
        global $language;
        $c = $this->config;
        $d = $c['direction'];
        $u = $c['union'];
        $html = $this->{$d['-1']}(true) . ' ' . $this->{$d['0']}(true) . ' ' . $this->{$d['1']}(true);
        return Hook::NS(strtolower(static::class) . $this->NS, [$html, $language, $this->bucket]);
    }

}