<?php namespace Genome;

class URL extends \URL {

    public function __construct() {
        foreach (_url_() as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function __set($key, $value = null) {
        $this->{$key} = $value;
    }

    public function __get($key) {
        return $this->{$key} ?? false;
    }

    public function __toString() {
        return $this->url;
    }

}