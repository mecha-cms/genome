<?php namespace Blog;

class Post extends \Genome {

    protected static function get_static($fail = []) {
        return \Shield::cargo('lot.post', $fail);
    }

    protected static function getByID_static($id, $fail = false) {}
    protected static function getByTime_static($time, $fail = false) {}
    protected static function getByKind_static($kind, $fail = false) {}
    protected static function getBySlug_static($slug, $fail = false) {}
    protected static function getByStatus_static($query, $fail = false) {}
    protected static function getByQuery_static($query, $fail = false) {}

    public static function __callStatic($kin, $lot) {
        $post = self::get_static((object) []);
        if (!self::kin($kin)) {
            return $post->{$kin} ?? array_shift($lot) ?? false;
        }
        return parent::__callStatic($kin, $lot);
    }

}