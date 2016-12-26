<?php namespace Blog;

class Post extends \Genome {

    public static function get($fail = []) {
        return \Shield::cargo('lot.post', $fail);
    }

    public static function getByID($id, $fail = false) {}
    public static function getByTime($time, $fail = false) {}
    public static function getByKind($kind, $fail = false) {}
    public static function getBySlug($slug, $fail = false) {}
    public static function getByStatus($query, $fail = false) {}
    public static function getByQuery($query, $fail = false) {}

    public static function __callStatic($kin, $lot) {
        $post = self::get((object) []);
        if (!self::kin($kin)) {
            return $post->{$kin} ?? array_shift($lot) ?? false;
        }
        return parent::__callStatic($kin, $lot);
    }

}