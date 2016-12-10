<?php

class Guardian extends Genome {

    public static $config = [
        'session' => [
            'token' => 'mecha.guardian.token',
            'user' => 'mecha.guardian.user'
        ]
    ];

    public static function user($k = null, $fail = false) {
        if ($log = Cookie::get(self::$config['session']['user'])) {
            return $k !== null ? ($log[$k] ?? $fail) : $log;
        }
        return $fail;
    }

    public static function users($id = null, $fail = false) {
        if ($folder = Folder::exist(ENGINE . DS . 'log' . DS . 'users')) {
            $ally = [];
            foreach (g($folder, 'log') as $file) {
                $id = Path::N($file);
                $ally[$id] = Page::open($file)->read('content', [
                    'id' => $id
                ], 'user:');
            }
            $ally = Hook::fire('users', $ally);
            return $id !== null ? ($ally[$id] ?? $fail) : $ally;
        } else {
            self::abort('Missing <code>' . ENGINE . DS . 'log' . DS . 'users</code> folder.');
        }
    }

    public static function token() {
        $log = ENGINE . DS . 'token.' . To::safe('file.name', self::user('id')) . '.log';
        $token = self::$config['session']['token'];
        $hash = File::open($log)->read(Session::get($token, self::hash_()));
        Session::set($token, $hash);
        return $hash;
    }

    public static function hash($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function check($token, $kick = false) {
        $s = Session::get(self::$config['session']['token'], "");
        if ($s === "" || $s !== $token) {
            Message::error('token');
            self::reject()->kick($kick ? trim($kick, '/') : URL::current());
        }
    }

    public static function reject() {
        $log = ENGINE . DS . 'token.' . To::safe('file.name', self::user('id')) . '.log';
        File::open($log)->delete();
        Session::reset(self::$config['session']['token']);
        Session::reset(self::$config['session']['user']);
    }

    public static function kick($path = "") {
        $url = URL::long(To::url($path));
        $G = ['url' => $url, 'source' => $path];
        Session::set('url.previous', URL::current());
        header('Location: ' . Hook::fire('kick', [$url, $G]));
        exit;
    }

    public static function abort($message, $exit = true) {
        echo Hook::fire('abort', [$message, $exit]);
        if ($exit) exit;
    }

}