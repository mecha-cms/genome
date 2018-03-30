<?php

class Message extends Genome {

    public static $id = 'mecha.message';
    public static $x = 0;

    const config = [
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
        $c = __c2f__(static::class, '_');
        $count = count($lot);
        $kin = array_shift($lot);
        $text = array_shift($lot);
        $s = array_shift($lot) ?: "";
        $k = array_shift($lot) ?: false;
        $i = $c . '_' . $kin . '_' . $text;
        $o = Language::get($i, $s, $k);
        $o = $o === $i ? $text : $o;
        if ($count === 1) {
            self::set('default', $kin);
        } else {
            $s = Session::get(self::$id, "");
            $ss = Hook::fire($c . '.set.' . $kin, [$o]);
            if (strpos($s, $ss) === false) {
                $s .= __replace__(HTML::unite(...self::$config['message']), [$kin, $ss]);
            }
            Session::set(self::$id, $s);
        }
        return new static;
    }

    public static function reset($error_x = true) {
        Session::reset(self::$id);
        if ($error_x) self::$x = 0;
    }

    public static function get($session_x = true) {
        $s = Session::get(self::$id, "");
        $output = Hook::fire(__c2f__(static::class, '_') . '.' . __FUNCTION__, [$s !== "" ? __replace__(HTML::unite(...self::$config['messages']), $s) : ""]);
        if ($session_x) self::reset();
        return $output;
    }

    public static function send($from, $to, $subject, $message) {
        if (empty($to) || (!is_array($to) && !Is::EMail($to))) {
            return false;
        }
        if (is_array($to)) {
            $s = "";
            if (__is_anemon_a__($to)) {
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
        $lot  = 'MIME-Version: 1.0' . N;
        $lot .= 'Content-Type: text/html; charset=ISO-8859-1' . N;
        $lot .= 'From: ' . $from . N;
        $lot .= 'Reply-To: ' . $from . N;
        $lot .= 'Return-Path: ' . $from . N;
        $lot .= 'X-Mailer: PHP/' . phpversion();
        $s = __c2f__(static::class, '_') . '.' . __FUNCTION__;
        $lot = Hook::fire($s . '.data', [$lot]);
        $data = Hook::fire($s . '.content', [$message]);
        return mail($to, $subject, $data, $lot);
    }

    public static function __callStatic($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        array_unshift($lot, $kin);
        return self::set(...$lot);
    }

}