<?php

class Elevator extends Genome {

    protected $bucket = [];

    public function __construct($input, $chunk = 5, $index = 0, $base = true) {
        ++$index;
        $input = Anemon::eat($input)->chunk($chunk);
        if ($base === true) {
            global $url;
            $base = $url->current;
        }
        $base = rtrim($base, '/');
        $this->bucket = [
            'previous' => !empty($input[$index - 2]) ? $base . '/' . ($index - 1) : null,
            'next' => !empty($input[$index]) ? $base . '/' . ($index + 1) : null
        ];
    }

    public function __get($key) {
        return array_key_exists($key, $this->bucket) ? $this->bucket[$key] : false;
    }

    public function __toString() {
        global $language;
        $html  = $this->bucket['previous'] ? HTML::a($language->previous, $this->bucket['previous'], false, ['rel' => 'previous']) : "";
        $html .= $this->bucket['next'] ? ' ' . HTML::a($language->next, $this->bucket['next'], false, ['rel' => 'next']) : "";
        return Hook::NS(strtolower(static::class), [$html, $language, $this->bucket]);
    }

}