<?php

class Message extends Genome {

    public static $x = 0;

    const config = [
        'session' => [
            'previous' => '71695985'
        ],
        'message' => [
            0 => 'p',
            1 => '%{1}%',
            2 => [
                'class[]' => ['container', 'block', 'message', 'message-%{0}%']
            ],
            3 => 1 // dent
        ],
        'messages' => [
            0 => 'div',
            1 => '%{0}%',
            2 => [
                'class[]' => ['container', 'block', 'messages', 'p']
            ]
        ]
    ];

    public static $config = self::config;

    public static function set(...$lot) {
        $id = self::$config['session']['previous'];
        $kin = array_shift($lot);
        $previous = Session::get($id, []);
        if (!isset($previous[$kin])) {
            $previous[$kin] = [];
        }
        $previous[$kin][] = $lot;
        Session::set($id, $previous);
        return new static;
    }

    public static function halt(...$lot) {
        ++self::$x;
        return self::set(...$lot);
    }

    public static function reset(string $kin = null) {
        Session::reset(self::$config['session']['previous'] . ($kin ? '.' . $kin : ""));
        return new static;
    }

    public static function get(string $kin = null, $reset = true) {
        global $language;
        $id = self::$config['session']['previous'];
        $c = c2f(static::class, '_', '/');
        $a = [];
        foreach (Session::get($id, []) as $k => $v) {
            if ($kin && $kin !== $k) {
                continue;
            }
            foreach ($v as $vv) {
                $text = $vv[0];
                $key = $c . '_' . $text;
                $t = $language->get($key, $vv[1] ?? [], $vv[2] ?? false);
                $s = candy(HTML::unite(self::$config['message']), [$k, $t === $key ? $text : $t]);
                $a[] = Hook::fire($c . '.' . __FUNCTION__ . '.' . $k, [$s]);
            }
        }
        if ($reset) self::reset($kin);
        $out = $a ? candy(HTML::unite(self::$config['messages']), [N . implode(N, $a) . N]) : "";
        return Hook::fire($c . '.' . __FUNCTION__, [$out]);
    }

    public static function send(string $from, $to, string $title, string $message) {
        if (empty($to) || (!is_array($to) && !Is::eMail($to))) {
            return false;
        }
        if (is_array($to)) {
            $s = "";
            if (fn\is\anemon_a($to)) {
                // ['foo@bar' => 'Foo Bar', 'baz@qux' => 'Baz Qux']
                foreach ($to as $k => $v) {
                    $s .= ', ' . $v . ' <' . $k . '>';
                }
                $to = substr($s, 2);
            } else {
                // ['foo@bar', 'baz@qux']
                $to = implode(', ', $to);
            }
        }
        $n = c2f(static::class, '_', '/') . '.';
        $header = Hook::fire($n . 'header', [[
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=ISO-8859-1',
            'From' => $from,
            'Reply-To' => $from,
            'Return-Path' => $from,
            'X-Mailer' => 'PHP/' . phpversion()
        ]]);
        $body = Hook::fire($n . 'body', [$message, $header]);
        $lot = "";
        foreach ($header as $k => $v) {
            $lot .= $k . ': ' . $v . N;
        }
        return mail($to, $title, $body, rtrim($lot, N));
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        array_unshift($lot, $kin);
        return self::set(...$lot);
    }

}