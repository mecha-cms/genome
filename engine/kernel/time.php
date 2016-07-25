<?php

class Time extends __ {

    public static function format($input, $format = 'Y-m-d H:i:s') {
        if (is_numeric($input)) return date($format, $input);
        if (substr_count($input, '-') === 5) {
            $s = explode('-', $input);
            $input = $s[0] . '-' . $s[1] . '-' . $s[2] . ' ' . $s[3] . ':' . $s[4] . ':' . $s[5];
        }
        return date($format, strtotime($input));
    }

    // Convert time to slug
    public static function slug($input) {
        if (is_string($input) && substr_count($input, '-') === 5) {
            return $input;
        }
        return self::format($input, 'Y-m-d-H-i-s');
    }

    public static function ago($input, $output = null, $compact = true) {
        $speak = new Gram();
        $date = new DateTime();
        $date->setTimestamp((int) self::format($input, 'U'));
        $interval = $date->diff(new DateTime('now'));
        $time = $interval->format('%y.%m.%d.%h.%i.%s');
        $time = e(explode('.', $time));
        $data = array(
            'year' => $time[0],
            'month' => $time[1],
            'day' => $time[2],
            'hour' => $time[3],
            'minute' => $time[4],
            'second' => $time[5]
        );
        if ($compact) {
            foreach ($data as $k => $v) {
                if ($v === 0) {
                    unset($data[$k]);
                } else {
                    break;
                }
            }
        }
        $results = [];
        foreach ($data as $k => $v) {
            $text = [$speak->{$k}, $speak->{$k . 's'}];
            $results[$k] = $v . ' ' . ($v === 1 ? $text[0] : $text[1]);
        }
        unset($data);
        return $output ? $results[$output] : $results;
    }

    public static function GMT($input, $format = 'Y-m-d H:i:s') {
        $time = new DateTime(self::format($input, 'c'));
        $time->setTimeZone(new DateTimeZone('UTC'));
        return $time->format($format);
    }

    public static function zone($zone = 'Asia/Jakarta') {
        date_default_timezone_set($zone);
    }

}