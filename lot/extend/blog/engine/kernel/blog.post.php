<?php namespace Blog;

class Post extends \Genome {

    protected static function get_($fail = []) {
        return \Shield::cargo('lot.post', $fail);
    }

    protected static function getByID_($id, $fail = false) {}
    protected static function getByTime_($time, $fail = false) {}
    protected static function getByKind_($kind, $fail = false) {}
    protected static function getBySlug_($slug, $fail = false) {}
    protected static function getByStatus_($query, $fail = false) {}
    protected static function getByQuery_($query, $fail = false) {}

    public static function __callStatic($kin, $lot) {
        $post = self::get_((object) []);
        if (!self::kin($kin)) {
            return $post->{$kin} ?? array_shift($lot) ?? false;
        }
        return parent::__callStatic($kin, $lot);
    }

}