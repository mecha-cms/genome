<?php

class Guardian extends Genome {

    public static $config = [
        'session' => [
            'token' => 'mecha.guardian.token',
            'user' => 'mecha.guardian.user'
        ]
    ];

    protected static function user_static($k = null, $fail = false) {
        if ($log = Cookie::get(self::$config['session']['user'])) {
            return $k !== null ? ($log[$k] ?? $fail) : $log;
        }
        return $fail;
    }

    protected static function users_static($id = null, $fail = false) {
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
            self::abort_static('Missing <code>' . ENGINE . DS . 'log' . DS . 'users</code> folder.');
        }
    }

    protected static function token_static() {
        $log = ENGINE . DS . 'token.' . To::safe('file.name', self::user_static('id')) . '.log';
        $token = self::$config['session']['token'];
        $hash = File::open($log)->read(Session::get($token, self::hash_static()));
        Session::set($token, $hash);
        return $hash;
    }

    protected static function hash_static($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    protected static function check_static($token, $kick = false) {
        $s = Session::get(self::$config['session']['token'], "");
        if ($s === "" || $s !== $token) {
            Message::error('token');
            self::reject_static()->kick_static($kick ? trim($kick, '/') : URL::current());
        }
    }

    protected static function reject_static() {
        $log = ENGINE . DS . 'token.' . To::safe('file.name', self::user_static('id')) . '.log';
        File::open($log)->delete();
        Session::reset(self::$config['session']['token']);
        Session::reset(self::$config['session']['user']);
    }

    protected static function kick_static($path = "") {
        $url = URL::long(To::url($path));
        $G = ['url' => $url, 'source' => $path];
        Session::set('url.previous', URL::current());
        header('Location: ' . Hook::fire('kick', [$url, $G]));
        exit;
    }

    protected static function abort_static($message, $exit = true) {
        echo Hook::fire('abort', [$message, $exit]);
        if ($exit) exit;
    }

}