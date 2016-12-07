<?php

class Message extends Genome {

    public static $id = 'mecha.message';
    public static $x = 0;

    public static $config = [
        'message' => [
            0 => 'p',
            1 => '$2$s',
            2 => [
                'classes' => ['container', 'block', 'message', 'message-%1$s']
            ],
            3 => 1 // dent
        ],
        'messages' => [
            0 => 'div',
            1 => '%1$s',
            2 => [
                'classes' => ['container', 'block', 'messages', 'p']
            ]
        ]
    ];

    protected static function set_(...$lot) {
        $count = count($lot);
        $kin = array_shift($lot);
        $text = array_shift($lot);
        $s = array_shift($lot) ?? "";
        $text = Language::get(__c2f__(static::class) . '_' . $kin . '_' . $text, (array) $s);
        if ($count === 1) {
            self::set_('default', $kin);
        } else {
            Session::set(self::$id, Session::get(self::$id, "") . sprintf(call_user_func_array('HTML::unite', self::$config['message']), $kin, $text));
        }
        return new static;
    }

    protected static function reset_($error_x = true) {
        Session::reset(self::$id);
        if ($error_x) self::$x = 0;
    }

    protected static function errors_($fail = false) {
        return self::$x > 0 ? self::$x : $fail;
    }

    protected static function get_($session_x = true) {
        $output = Session::get(self::$id, "") !== "" ? HTML::$begin . sprintf(call_user_func_array('HTML::unite', self::$config['messages']), Session::get(self::$id)) . HTML::$end : "";
        if ($session_x) self::reset();
        return $output;
    }

    protected static function send_($from, $to, $subject, $message) {
        if (Is::void($to) || Is::email($to)) return false;
        $meta  = 'MIME-Version: 1.0' . N;
        $meta .= 'Content-Type: text/html; charset=ISO-8859-1' . N;
        $meta .= 'From: ' . $from . N;
        $meta .= 'Reply-To: ' . $from . N;
        $meta .= 'Return-Path: ' . $from . N;
        $meta .= 'X-Mailer: PHP/' . phpversion();
        $s = __c2f__(static::class) . ':' . __METHOD__;
        $meta = Hook::NS($s . '.meta', $meta);
        $data = Hook::NS($s . '.data', $message);
        return mail($to, $subject, $data, $meta);
    }

    public static function __callStatic($kin, $lot) {
        if (!self::kin($kin)) {
            array_unshift($lot, $kin);
            return call_user_func_array('self::set_', $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}