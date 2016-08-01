<?php

class Time extends __ {

    public static function format($input, $f = 'Y-m-d H:i:s') {
        if (is_numeric($input)) return date($f, $input);
        if (substr_count($input, '-') === 5) {
            $s = explode('-', $input);
            $input = $s[0] . '-' . $s[1] . '-' . $s[2] . ' ' . $s[3] . ':' . $s[4] . ':' . $s[5];
        }
        return date($f, strtotime($input));
    }

    public static function slug($input) {
        if (is_string($input) && substr_count($input, '-') === 5) {
            return $input;
        }
        return self::format($input, 'Y-m-d-H-i-s');
    }

    public static function ago($input, $key = null, $compact = true) {
        $speak = Speak::get();
        $date = new DateTime();
        $date->setTimestamp((int) self::format($input, 'U'));
        $i = $date->diff(new DateTime('now'));
        $t = $i->format('%y.%m.%d.%h.%i.%s');
        $t = e(explode('.', $t));
        $data = [
            'y' => $t[0],
            'm' => $t[1],
            'd' => $t[2],
            'h' => $t[3],
            'i' => $t[4],
            's' => $t[5]
        ];
        if ($compact) {
            foreach ($data as $k => $v) {
                if ($v === 0) {
                    unset($data[$k]);
                } else {
                    break;
                }
            }
        }
        $output = [];
        foreach ($data as $k => $v) {
            $text = [$speak->{$k}, $speak->{$k . 's'}];
            $output[$k] = $v . ' ' . ($v === 1 ? $text[0] : $text[1]);
        }
        unset($data);
        return $key ? $output[$key] ?? end($output) : $output;
    }

    public static function GMT($input, $format = 'Y-m-d H:i:s') {
        $time = new DateTime($this->format($input, 'c'));
        $time->setTimeZone(new DateTimeZone('UTC'));
        return $time->format($format);
    }

    public static function zone($zone = 'Asia/Jakarta') {
        date_default_timezone_set($zone);
    }

}