<?php

class Guardian extends Genome {

    public static $config = [
        'session' => [
            'token' => 'mecha.guardian.token',
            'user' => 'mecha.guardian.user'
        ]
    ];

    protected static function user_($k = null, $fail = false) {
        if ($log = Cookie::get(self::$config['session']['user'])) {
            return $k !== null ? ($log[$k] ?? $fail) : $log;
        }
        return $fail;
    }

    protected static function users_($id = null, $fail = false) {
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
            self::abort_('Missing <code>' . ENGINE . DS . 'log' . DS . 'users</code> folder.');
        }
    }

    protected static function token_() {
        $log = ENGINE . DS . 'token.' . To::safe('file.name', self::user_('id')) . '.log';
        $token = self::$config['session']['token'];
        $hash = File::open($log)->read(Session::get($token, self::hash_()));
        Session::set($token, $hash);
        return $hash;
    }

    protected static function hash_($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    protected static function check_($token, $kick = false) {
        $s = Session::get(self::$config['session']['token'], "");
        if ($s === "" || $s !== $token) {
            Message::error('token');
            self::reject_()->kick_($kick ? trim($kick, '/') : URL::current());
        }
    }

    protected static function reject_() {
        $log = ENGINE . DS . 'token.' . To::safe('file.name', self::user_('id')) . '.log';
        File::open($log)->delete();
        Session::reset(self::$config['session']['token']);
        Session::reset(self::$config['session']['user']);
    }

    protected static function kick_($path = "") {
        $url = URL::long(To::url($path));
        $G = ['url' => $url, 'source' => $path];
        Session::set('url.previous', URL::current());
        header('Location: ' . Hook::fire('kick', [$url, $G]));
        exit;
    }

    protected static function abort_($message, $exit = true) {
        echo Hook::fire('abort', [$message, $exit]);
        if ($exit) exit;
    }

}