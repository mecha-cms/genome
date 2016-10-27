<?php namespace Genome;

class Date extends \Genome {

    protected $date = "";

    public function __construct($s = null) {
        $this->date = $s ?? $_SERVER['REQUEST_TIME'] ?? time();
    }

    public function format($format = 'Y-m-d H:i:s') {
        $date = $this->date;
        if (is_numeric($date)) return date($format, $date);
        if (substr_count($date, '-') === 5) {
            return \DateTime::createFromFormat('Y-m-d-H-i-s', $date)->format($format);
        }
        return date($format, strtotime($date));
    }

    public function extract($key = null, $fail = false) {
        $language = new Language;
        $months_long = $language->months_long;
        $days_long = $language->days_long;
        $months_short = $language->months_short;
        $days_short = $language->days_short;
        list(
            $year,
            $year_short,
            $month,
            $day,
            $hour_24,
            $hour_12,
            $minute,
            $second,
            $AP,
            $d
        ) = explode('.', $this->format('Y.y.m.d.H.h.i.s.A.w'));
        $month_long = $months_long[(int) $month - 1];
        $month_short = $months_short[(int) $month - 1];
        $day_long = $days_long[(int) $d];
        $day_short = $days_short[(int) $d];
        $output = [
            'UNIX' => (int) $this->format('U'),
            'W3C' => $this->format('c'),
            'GMT' => $this->GMT('Y-m-d H:i:s'),
            'slug' => $year . '-' . $month . '-' . $day . '-' . $hour_24 . '-' . $minute . '-' . $second,
            'year' => $year,
            'year_short' => $year_short,
            'month' => $month,
            'day' => $day,
            'month_long' => $month_long,
            'day_long' => $day_long,
            'month_short' => $month_short,
            'day_short' => $day_short,
            'hour' => $hour_24,
            'hour_12' => $hour_12,
            'hour_24' => $hour_24, // alias for `hour`
            'minute' => $minute,
            'second' => $second,
            'year_number' => (int) $year,
            'month_number' => (int) $month,
            'day_number' => (int) $day,
            'hour_number' => (int) $hour_24,
            'hour_12_number' => (int) $hour_12,
            'hour_24_number' => (int) $hour_24,
            'minute_number' => (int) $minute,
            'second_number' => (int) $second,
            'AM_PM' => $AP,
            'FORMAT_1' => $day_long . ', ' . $day . ' ' . $month_long . ' ' . $year,
            'FORMAT_2' => $day_long . ', ' . $month_long . ' ' . $day . ', ' . $year,
            'FORMAT_3' => $year . '/' . $month . '/' . $day . ' ' . $hour_24 . ':' . $minute . ':' . $second,
            'FORMAT_4' => $year . '/' . $month . '/' . $day . ' ' . $hour_12 . ':' . $minute . ':' . $second . ' ' . $AP,
            'FORMAT_5' => $hour_24 . ':' . $minute,
            'FORMAT_6' => $hour_12 . ':' . $minute . ' ' . $AP
        ];
        if (!empty(Date::$formats)) {
            foreach (Date::$formats as $k => $v) {
                $output[$k] = call_user_func($v, $output, $language);
            }
        }
        return $key !== null ? ($output[$key] ?? $fail) : $output;
    }

    public function ago($key = null, $fail = false, $compact = true) {
        $language = new Language;
        $date = new DateTime();
        $date->setTimestamp((int) $this->format('U'));
        $interval = $date->diff(new DateTime('now'));
        $time = $interval->format('%y.%m.%d.%h.%i.%s');
        $time = explode('.', $time);
        $data = [
            'year' => $time[0],
            'month' => $time[1],
            'day' => $time[2],
            'hour' => $time[3],
            'minute' => $time[4],
            'second' => $time[5]
        ];
        $output = [];
        foreach ($data as $k => $v) {
            if ($compact && $v === '0') continue;
            $output[$k] = $v . ' ' . ($v === '1' ? $language->{$k} : $language->{$k . 's'});
        }
        unset($data);
        return $key !== null ? ($output[$key] ?? $fail) : $output;
    }

    public function GMT($format = 'Y-m-d H:i:s') {
        $date_GMT = new \DateTime($this->format('c'));
        $date_GMT->setTimeZone(new \DateTimeZone('UTC'));
        return $date_GMT->format($format);
    }

    public function __get($key) {
        return $this->extract($key);
    }

    public function __set($key, $value = null) {}

    public function __toString() {
        return $this->date;
    }

    public function __invoke($format = 'Y-m-d H:i:s') {
        return $this->format($format);
    }

}