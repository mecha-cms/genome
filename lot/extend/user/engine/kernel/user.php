<?php

class User extends Genome {

    const ID = '@';

    public static function read($id, $lot = [], $fail = false) {
        global $url;
        $user = ENGINE . DS . 'log' . DS . 'user';
        $state = Extend::state(Path::D(__DIR__, 2));
        if ($path = File::exist($user . DS . $id . '.page')) {
            return Page::open($path)->data('url', function($data) use($user, $state, $id, $url) {
                $s = str_replace([$user . DS, $user], "", Path::D($data['path']));
                return $url . '/' . $state['slug'] . '/' . ($s ? '/' . $s : "") . $id;
            })->read($lot, 'user');
        }
        return $fail;
    }

    protected $lot = [];
    protected $id = "";

    public function __construct($id, $lot = []) {
        $this->lot = self::read($id, $lot, []);
        $this->id = $id;
    }

    public function __call($key, $lot) {
        $fail = array_shift($lot) ?: false;
        $fail_alt = array_shift($lot) ?: false;
        if (is_string($fail) && strpos($fail, '~') === 0) {
            return call_user_func(substr($fail, 1), array_key_exists($key, $this->lot) ? o($this->lot[$key]) : $fail_alt);
        } else if ($fail instanceof \Closure) {
            return call_user_func($fail, array_key_exists($key, $this->lot) ? o($this->lot[$key]) : $fail_alt);
        }
        return array_key_exists($key, $this->lot) ? o($this->lot[$key]) : $fail;
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return array_key_exists($key, $this->lot) ? o($this->lot[$key]) : "";
    }

    public function __toString() {
        $bucket = $this->lot;
        $id = $this->id;
        return array_key_exists('author', $bucket) ? $bucket['author'] : self::ID . (array_key_exists('id', $bucket) ? $bucket['id'] : $this->id);
    }

}