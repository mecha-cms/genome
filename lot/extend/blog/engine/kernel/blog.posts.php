<?php namespace Blog;

class Posts extends \Genome {

    public static function get($i = null, $fail = []) {
        $i = $i ?? \Config::get('page.chunk', 5);
        $posts = \Shield::cargo('lot.posts', $fail);
        return \Anemon::eat($posts)->chunk($i, 0);
    }

    public static function getByTime($time, $fail = false) {}
    public static function getByKind($kind, $fail = false) {}
    public static function getByStatus($query, $fail = false) {}
    public static function getByQuery($query, $fail = false) {}

}