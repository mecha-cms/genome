<?php

class Notify extends DNA {

    public $session = 'message';
    public $x = 0;

    public $config = [
        'message' => '<p class="message message-%1$s cl cf">%2$s</p>',
        'messages' => '<div class="messages p cl cf">%1$s</div>'
    ];

    public function add($kin, $text = "") {
        if (func_num_args() === 1) {
            $this->add('default', $kin);
        } else {
            Session::set($this->message, Session::get($this->message, "") . sprintf($this->config['message'], $kin, $text));
        }
        return $this;
    }

    public function clear($clear_errors = true) {
        Session::reset($this->message);
        if ($clear_errors) $this->x = 0;
    }

    public function errors($fail = false) {
        return $this->x > 0 ? $this->x : $fail;
    }

    public function read($clear_sessions = true) {
        $output = Session::get($this->message, "") !== "" ? CELL_BEGIN . sprintf($this->config['messages'], Session::get($this->message)) . CELL_END : "";
        if ($clear_sessions) $this->clear();
        return $output;
    }

    public function send($from, $to, $subject, $message, $NS = "") {
        if (Is::void($to) || Is::email($to)) return false;
        $header  = "MIME-Version: 1.0\n";
        $header .= "Content-Type: text/html; charset=ISO-8859-1\n";
        $header .= "From: " . $from . "\n";
        $header .= "Reply-To: " . $from . "\n";
        $header .= "Return-Path: " . $from . "\n";
        $header .= "X-Mailer: PHP/" . phpversion();
        $header = Filter::apply($FP . 'notify.email.header', $header);
        $message = Filter::apply($FP . 'notify.email.message', $message);
        return mail($to, $subject, $message, $header);
    }

}