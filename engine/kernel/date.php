<?php

class Date extends Genome {

    public static function format($input, $format = 'Y-m-d H:i:s') {
        $date = new Genome\Date($input);
        return $date->format($format);
    }

    public static function slug($input) {
        return self::format($input, 'Y-m-d-H-i-s');
    }

}