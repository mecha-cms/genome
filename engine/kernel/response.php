<?php

class Response extends Genome {

    public static function status(int $i = null) {
        if (isset($i)) {
            http_response_code($i);
        }
        return http_response_code();
    }

    public static function type(string $type = null, array $lot = []) {
        if (!isset($type)) {
            return Header::get('Content-Type');
        }
        foreach ($lot as $k => $v) {
            $type .= '; ' . $k . '=' . $v;
        }
        Header::set('Content-Type', $type);
    }

}