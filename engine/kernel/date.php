<?php

final class Date extends Genome {

    private static $locale;
    private static $zone;

    public $o;
    public $parent;
    public $source;

    public function ISO8601() {
        return $this->format('c');
    }

    public function __call(string $kin, array $lot = []) {
        if ($v = self::_($kin)) {
            if (is_string($v = $v[0]) && strpos($v, '%') !== false) {
                return $this->__invoke($v);
            }
        }
        return parent::__call($kin, $lot);
    }

    public function __construct($date) {
        if (is_numeric($date)) {
            $this->source = date(DATE_FORMAT, $date);
        } else if (strlen($date) >= 19 && substr_count($date, '-') === 5) {
            $this->source = \DateTime::createFromFormat('Y-m-d-H-i-s', $date)->format(DATE_FORMAT);
        } else {
            $this->source = date(DATE_FORMAT, strtotime($date));
        }
    }

    public function __invoke(string $pattern = null) {
        return strftime($pattern ?? '%Y-%m-%d %H:%I:%S', strtotime($this->source));
    }

    public function __toString() {
        return $this->source . "";
    }

    public function date() {
        return $this->format('d');
    }

    public function day($type = null) {
        return $this->__invoke(is_string($type) ? '%A' : '%u');
    }

    public function format(string $format = DATE_FORMAT) {
        return date($format, strtotime($this->source)); // Generic PHP date formatter
    }

    public function hour($type = null) {
        return $this->format($type === 12 ? 'h' : 'H');
    }

    public function minute() {
        return $this->format('i');
    }

    public function month($type = null) {
        return $this->__invoke(is_string($type) ? '%B' : '%m');
    }

    public function second() {
        return $this->format('s');
    }

    public function slug($separator = '-') {
        return strtr($this->source, '- :', str_repeat($separator, 3));
    }

    public function to(string $zone = 'UTC') {
        $date = new \DateTime($this->source);
        $date->setTimeZone(new \DateTimeZone($zone));
        if (!isset($this->o[$zone])) {
            $this->o[$zone] = new static($date->format(DATE_FORMAT));
            $this->o[$zone]->parent = $this;
        }
        return $this->o[$zone];
    }

    public function year() {
        return $this->format('Y');
    }

    public static function from($in) {
        return new static($in);
    }

    public static function locale($locale = null) {
        if (!isset($locale)) {
            return self::$locale ?? DATE_LOCALE;
        }
        setlocale(LC_TIME, self::$locale = (array) ($locale ?? DATE_LOCALE));
    }

    public static function zone(string $zone = null) {
        if (!isset($zone)) {
            return self::$zone ?? DATE_ZONE;
        }
        return date_default_timezone_set(self::$zone = $zone ?? DATE_ZONE);
    }

}