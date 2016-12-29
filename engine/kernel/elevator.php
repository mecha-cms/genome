<?php

class Elevator extends Genome {

    public $config = [
        'up' => 'up',
        '0' => "",
        'down' => 'down'
    ];

    protected $bucket = [];
    protected $NS = "";

    public function __construct($input, $chunk = 5, $index = 0, $path = true, $config = [], $NS = "") {
        extract(Anemon::extend($this->config, $config));
        global $url;
        $input = Anemon::eat($input)->chunk($chunk);
        if ($path === true) {
            $path = $url->current;
        }
        $path = rtrim($path, '/');
        $this->bucket = [
            $up => !empty($input[$index - 1]) ? $path . '/' . $index : null,
            '0' => $path !== $url->current ? $path : null,
            $down => !empty($input[$index + 1]) ? $path . '/' . ($index + 2) : null
        ];
        $this->NS = $NS ? Anemon::NS . $NS : "";
    }

    public function __get($key) {
        return array_key_exists($key, $this->bucket) ? $this->bucket[$key] : false;
    }

    public function __toString() {
        global $language;
        extract($this->config);
        $html  = $this->bucket[$up] ? HTML::a('&#x25B2;', $this->bucket[$up]) : HTML::span('&#x25B2;');
        $html .= ' ' . ($this->bucket['0'] ? HTML::a('&#x25C6;', $this->bucket['0']) : HTML::span('&#x25C6;')) . ' ';
        $html .= $this->bucket[$down] ? HTML::a('&#x25BC;', $this->bucket[$down]) : HTML::span('&#x25BC;');
        return Hook::NS(strtolower(static::class) . $this->NS, [$html, $language, $this->bucket]);
    }

}