<?php namespace Blog\Get\Post;

class By extends \Genome {

    protected static function explore($x, $pattern = '*') {
        $x = l(str_replace(' ', "", $x));
        if (strpos($x, ',') !== false) {
            $x = '{' . $x . '}';
            $flag = GLOB_NOSORT | GLOB_BRACE;
        } else {
            $flag = GLOB_NOSORT;
        }
        return glob(POST . DS . 'blog' . DS . $pattern . '.' . $x, $flag);
    }

    public static function ID($id, $x = 'txt', $fail = false) {
        foreach (self::explore($x) as $post) {
            list($t, $k, $s) = explode('_', Path::N($post));
            if ($t === Date::slug($t)) return $post;
        }
        return $fail;
    }

    public static function time($time, $x = 'txt', $fail = false) {
        foreach (self::explore($x, $time . '*') as $post) {
            list($t, $k, $s) = explode('_', Path::N($post));
            if (strpos($t, $time) === 0) return $post;
        }
        return $fail;
    }

    public static function kind($kind, $x = 'txt', $fail = false) {}

    public static function slug($slug, $x = 'txt', $fail = false) {
        foreach (self::explore($x) as $post) {
            list($t, $k, $s) = explode('_', Path::N($post));
            if ($s === $slug) return $post;
        }
        return $fail;
    }

    public static function key($word, $x = 'txt', $fail = false) {
        foreach (self::explore($x) as $post) {
            if (strpos(Path::N($post), $word) !== false) return $post;
        }
        return $fail;
    }

}