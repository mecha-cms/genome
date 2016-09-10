<?php

class Guardian extends Genome {

    public static $config = [
        'session' => 'token'
    ];

    public static function author($k = null, $fail = false) {
        if ($log = Cookie::get(self::$config['session']['author'])) {
            return $k !== null ? ($log[$k] ?? $fail) : $log;
        }
        return $fail;
    }

    public static function authors($id = null, $fail = false) {
        if($folder = Folder::exist(ENGINE . DS . 'log' . DS . 'author')) {
            $ally = [];
            foreach (glob($folder . DS . '*.log', GLOB_NOSORT | GLOB_BRACE) as $file) {
                $id = Path::N($file);
                $ally[$id] = Page::open($file)->read('content', [
                    'id' => $id
                ], 'author:');
            }
            $ally = Hook::NS('authors', $ally);
            return $id !== null ? ($ally[$id] ?? $fail) : $ally;
        } else {
            self::abort('Missing <code>' . ENGINE . DS . 'log' . DS . 'author</code> folder.');
        }
    }

    public static function token() {
        $log = ENGINE . DS . 'token.' . To::safe('file.name', self::author('id')) . '.log';
        $token = self::$config['session'];
        $hash = File::open($log)->read(Session::get($token, self::hash()));
        Session::set($token, $hash);
        return $hash;
    }

    public static function hash($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function check($token, $kick = false) {
        $s = Session::get(self::$config['session'], "");
        if ($s === "" || $s !== $token) {
            Notify::error('token');
            self::reject()->kick($kick ? trim($kick, '/') : URL::current());
        }
    }

    public static function reject() {
        $log = ENGINE . DS . 'token.' . To::safe('file.name', self::author('id')) . '.log';
        File::open($log)->delete();
        Session::reset(self::$config['session']);
    }

    public static function kick($path = "") {
        $url = URL::long(To::url($path));
        $url = Filter::apply('guardian:kick', $url);
        $G = ['url' => $url, 'source' => $path];
        Session::set('url.previous', URL::current());
        header('Location: ' . Hook::fire('kick', $url));
        exit;
    }

}