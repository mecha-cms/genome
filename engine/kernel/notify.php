<?php

class Notify extends Socket {

    public static $session = 'message';
    public static $x = 0;

    public static $config = [
        'message' => '<p class="message message-%1$s cl cf">%2$s</p>',
        'messages' => '<div class="messages p cl cf">%1$s</div>'
    ];

    public static function set(...$lot) {
        $kin = array_shift($lot);
        $text = array_shift($lot) ?? "";
        if (count($lot) === 1) {
            self::set('default', $lot[0]);
        } else {
            Session::set(self::$message, Session::get(self::$message, "") . sprintf(self::$config['message'], $kin, $text));
        }
        return new static;
    }

    public static function reset($clear_errors = true) {
        Session::reset(self::$message);
        if ($clear_errors) self::$x = 0;
    }

    public static function errors($fail = false) {
        return self::$x > 0 ? self::$x : $fail;
    }

    public static function get($clear_sessions = true) {
        $output = Session::get(self::$message, "") !== "" ? CELL_BEGIN . sprintf(self::$config['messages'], Session::get(self::$message)) . CELL_END : "";
        if ($clear_sessions) self::reset();
        return $output;
    }

    public static function send($from, $to, $subject, $message, $NS = "") {
        if (Is::void($to) || Is::email($to)) return false;
        $head  = 'MIME-Version: 1.0' . N;
        $head .= 'Content-Type: text/html; charset=ISO-8859-1' . N;
        $head .= 'From: ' . $from . N;
        $head .= 'Reply-To: ' . $from . N;
        $head .= 'Return-Path: ' . $from . N;
        $head .= 'X-Mailer: PHP/' . phpversion();
        $head = Hook::NS($NS . 'notify.email.head', [], $head);
        $body = Hook::NS($NS . 'notify.email.body', [], $body);
        return mail($to, $subject, $body, $head);
    }

}