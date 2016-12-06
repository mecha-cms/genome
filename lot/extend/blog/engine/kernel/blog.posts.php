<?php namespace Blog;

class Posts extends \Genome {

    protected static function get_static($i = null, $fail = []) {
        $i = $i ?? \Config::get('page.chunk', 5);
        $posts = \Shield::cargo('lot.posts', $fail);
        return \Anemon::eat($posts)->chunk($i, 0);
    }

    protected static function getByTime_static($time, $fail = false) {}
    protected static function getByKind_static($kind, $fail = false) {}
    protected static function getByStatus_static($query, $fail = false) {}
    protected static function getByQuery_static($query, $fail = false) {}

}