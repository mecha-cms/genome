<?php namespace Blog;

class Posts extends \Genome {

    protected static function get_($i = null, $fail = []) {
        $i = $i ?? \Config::get('page.chunk', 5);
        $posts = \Shield::cargo('lot.posts', $fail);
        return \Anemon::eat($posts)->chunk($i, 0);
    }

    protected static function getByTime_($time, $fail = false) {}
    protected static function getByKind_($kind, $fail = false) {}
    protected static function getByStatus_($query, $fail = false) {}
    protected static function getByQuery_($query, $fail = false) {}

}