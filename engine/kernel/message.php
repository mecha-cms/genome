<?php

class Message extends Genome {

    const session = 'message';

    public static $x = 0;

    public function __toString() {
        return self::get("", false);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        array_unshift($lot, $kin);
        return self::set(...$lot);
    }

    public static function get(string $kin = null, $reset = true) {
        $c = c2f(static::class, '_', '/');
        $out = [];
        foreach ((array) Session::get(self::session) as $k => $v) {
            if ($kin && $kin !== $k) {
                continue;
            }
            foreach ($v as $vv) {
                $text = $vv[0];
                $key = $c . '_' . $k . '_' . $text;
                $t = Language::get($key, $vv[1] ?? [], $vv[2] ?? false);
                $out[] = '<message type="' . $k . '">' . ($t === $key ? $text : $t) . '</message>';
            }
        }
        $reset && self::reset($kin);
        return implode(N, $out);
    }

    public static function halt(...$lot) {
        ++self::$x;
        return self::set(...$lot);
    }

    public static function reset(string $kin = null) {
        Session::reset(self::session . ($kin ? '.' . $kin : ""));
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
        $c = static::class;
        $n = c2f($c, '_', '/') . '.';
        $header = Hook::fire($n . 'header', [[
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=ISO-8859-1',
            'From' => $from,
            'Reply-To' => $from,
            'Return-Path' => $from,
            'X-Mailer' => 'PHP/' . phpversion()
        ]], null, $c);
        $body = Hook::fire($n . 'body', [$message, $header], null, $c);
        $lot = [];
        foreach ($header as $k => $v) {
            $lot[$k] = $k . ': ' . $v;
        }
        return mail($to, $title, $body, implode("\r\n", $lot));
    }

    public static function set(...$lot) {
        $kin = array_shift($lot);
        $previous = Session::get(self::session, []);
        if (!isset($previous[$kin])) {
            $previous[$kin] = [];
        }
        $previous[$kin][] = $lot;
        Session::set(self::session, $previous);
    }

}