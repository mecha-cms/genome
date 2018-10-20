<?php

class Date extends Genome {

    public $lot = [];
    public $source = null;

    protected static $zone = "";
    protected static $pattern = '%Y%-%M%-%D% %~h%:%m%:%s%';

    protected function extract() {
        if (!$this->lot) {
            global $language;
            $i = explode('.', $this->format('Y.m.d.H.i.s.A.w.h.e'));
            $this->lot = [
                '%year%' => $i[0],
                '%month%' => $i[1],
                '%day%' => $i[2],
                '%hour%' => $i[3],
                '%minute%' => $i[4],
                '%second%' => $i[5],
                '%noon%' => $i[6],
                '%week%' => $i[7],
                '%zone%' => $i[9],
                '%~M%' => $language->months_long[(int) $i[1] - 1],
                '%~D%' => $language->days_long[(int) $i[7]],
                '%~h%' => $i[3],
                '%Y%' => $i[0],
                '%M%' => $i[1],
                '%D%' => $i[2],
                '%m%' => $i[4],
                '%s%' => $i[5],
                '%n%' => $i[6],
                '%w%' => $i[7],
                '%h%' => $i[8],
                '%z%' => $i[9]
            ];
        }
        return $this;
    }

    public function month($type = null) {
        return $this->extract()->lot['%' . (is_string($type) ? '~M' : 'M') . '%'];
    }

    public function day($type = null) {
        $this->extract();
        if (is_string($type)) {
            return $this->lot['%~D%'];
        }
        return $this->lot['%' . ($type === 7 ? 'w' : 'd') . '%'];
    }

    public function hour($type = null) {
        return $this->extract()->lot['%' . ($type === 12 ? 'h' : '~h') . '%'];
    }

    public function W3C() {
        return $this->format('c');
    }

    public function GMT() {
        $date = new \DateTime($this->W3C());
        $date->setTimeZone(new \DateTimeZone('UTC'));
        $this->source = $date->format(DATE_WISE);
        return $this;
    }

    public function pattern(string $pattern = null) {
        $this->extract();
        $pattern = $pattern ?? self::$pattern;
        if (isset($this->lot[$pattern])) {
            return $this->lot[$pattern];
        }
        return strtr(strtr(strtr($pattern, '\%', X), $this->lot), X, '%');
    }

    public function format($format = DATE_WISE) {
        return date($format, strtotime($this->source)); // Generic PHP date formatter
    }

    public function __construct(string $date = null) {
        if (!isset($date)) {
            $this->source = date(DATE_WISE, $_SERVER['REQUEST_TIME'] ?? time());
        } else if (is_numeric($date)) {
            $this->source = date(DATE_WISE, $date);
        } else if (strlen($date) >= 19 && substr_count($date, '-') === 5) {
            $this->source = \DateTime::createFromFormat('Y-m-d-H-i-s', $date)->format(DATE_WISE);
        } else {
            $this->source = date(DATE_WISE, strtotime($date));
        }
    }

    public function __call($kin, $lot = []) {
        $this->extract();
        $k = '%' . $kin . '%';
        if (array_key_exists($k, $this->lot)) {
            return $this->extract()->lot[$k];
        } else if ($v = self::_($kin)) {
            if (is_string($v = $v[0]) && strpos($v, '%') !== false) {
                return $this->pattern($v);
            }
        }
        return parent::__call($kin, $lot);
    }

    public function __invoke(string $pattern = null) {
        return $this->pattern($pattern ?? self::$pattern);
    }

    public function __toString() {
        return $this->source . "";
    }

    public static function zone(string $zone = null) {
        if (!isset($zone)) {
            return self::$zone;
        }
        return date_default_timezone_set(self::$zone = $zone);
    }

}