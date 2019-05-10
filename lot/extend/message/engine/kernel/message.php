<?php

final class Message extends Genome {

    private static function t(array $lot, string $kin) {
        $out = "";
        foreach ($lot as $v) {
            $text = array_shift($v);
            $t = rtrim('message-' . $kin . '-' . $text, '-');
            $vars = array_shift($v) ?? [];
            $preserve_case = array_shift($lot);
            $m = Language::get($t, $vars, $preserve_case);
            $out .= '<message type="' . $kin . '">' . ($m === $t ? $text : $m) . '</message>';
        }
        return $out;
    }

    public function __toString() {
        return self::get() . "";
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        array_unshift($lot, $kin);
        return self::set(...$lot);
    }

    public static function get($kin = null) {
        if (is_array($kin)) {
            $out = "";
            foreach ($kin as $v) {
                if (isset($_SESSION['message'][$v])) {
                    $out .= self::t($_SESSION['message'][$v], $v);
                    unset($_SESSION['message'][$v]);
                }
            }
            return $out;
        }
        if (isset($kin) && isset($_SESSION['message'][$kin])) {
            $out = self::t($_SESSION['message'][$kin], $kin);
            unset($_SESSION['message'][$kin]);
            return $out;
        }
        if (isset($_SESSION['message'])) {
            $out = "";
            foreach ((array) $_SESSION['message'] as $k => $v) {
                $out .= self::t($v, $k);
                unset($_SESSION['message'][$k]);
            }
            return $out;
        }
        return null;
    }

    public static function send(array $data, array $header = []) {
        extract($data, EXTR_SKIP);
        if (!isset($to)) {
            return false;
        }
        if (is_array($to)) {
            $s = "";
            if (_\is\anemon_a($to)) {
                // ['foo@bar' => 'Foo Bar', 'baz@qux' => 'Baz Qux']
                foreach ($to as $k => $v) {
                    $s .= ', ' . $v . ' <' . $k . '>';
                }
                $to = substr($s, 2);
            } else {
                // ['foo@bar', 'baz@qux']
                $to = implode(', ', $to);
            }
        } else if (!Is::eMail($to)) {
            return false;
        }
        $lot = [];
        foreach (array_replace_recursive([
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=ISO-8859-1',
            'From' => $from,
            'Reply-To' => $from,
            'Return-Path' => $from,
            'X-Mailer' => 'PHP/' . phpversion()
        ], $header) as $k => $v) {
            $lot[] = $k . ': ' . $v;
        }
        return mail($to, $title ?? "", $content ?? "", implode("\r\n", $lot));
    }

    public static function set(...$lot) {
        $kin = array_shift($lot);
        $_SESSION['message'][$kin][] = $lot;
    }

    public static function let($kin = null) {
        if (is_array($kin)) {
            foreach ($kin as $v) {
                unset($_SESSION['message'][$v]);
            }
        } else if (isset($kin)) {
            unset($_SESSION['message'][$kin]);
        } else {
            unset($_SESSION['message']);
        }
    }

}