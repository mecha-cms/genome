<?php

class HTTP extends Genome {

    const config = [
        'session' => [
            'request' => 'mecha.request'
        ]
    ];

    public static $config = self::config;

    public static $message = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing', // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status', // RFC4918
        208 => 'Already Reported', // RFC5842
        226 => 'IM Used', // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect', // RFC-reschke-http-status-308-07
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I’m a teapot', // RFC2324
        422 => 'Unprocessable Entity', // RFC4918
        423 => 'Locked', // RFC4918
        424 => 'Failed Dependency', // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal', // RFC2817
        426 => 'Upgrade Required', // RFC2817
        428 => 'Precondition Required', // RFC6585
        429 => 'Too Many Requests', // RFC6585
        431 => 'Request Header Fields Too Large', // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)', // RFC2295
        507 => 'Insufficient Storage', // RFC4918
        508 => 'Loop Detected', // RFC5842
        510 => 'Not Extended', // RFC2774
        511 => 'Network Authentication Required' // RFC6585
    ];

    public static function status($i = null) {
        if (is_int($i) && isset(self::$message[$i])) {
            if (strpos(PHP_SAPI, 'cgi') !== false) {
                header('Status: ' . $i . ' ' . self::$message[$i]);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' ' . $i . ' ' . self::$message[$i]);
            }
        } else if (!isset($i)) {
            return http_response_code();
        }
        return new static;
    }

    public static function query($query = null, $c = []) {
        $c = array_replace(['?', '&amp;', '=', ""], $c);
        if (!isset($query)) {
            $query = $GLOBALS['URL']['query'];
            return str_replace(['?', '&', '='], $c, $query);
        }
        return To::query($query ? array_replace_recursive($_GET, (array) $query) : $_GET);
    }

    protected static function _q($a, $k) {
        $output = [];
        $s = $k ? '%5D' : "";
        foreach ($a as $kk => $vv) {
            $kk = urlencode($kk);
            if (is_array($vv)) {
                $output = array_merge($output, self::_q($vv, $k . $kk . $s . '%5B'));
            } else {
                $output[$k . $kk . $s] = $vv;
            }
        }
        return $output;
    }

    public static function header($key = null, $value = null) {
        if (!isset($ket)) {
            $output = [];
            foreach (headers_list() as $v) {
                $a = explode(':', $v, 2);
                $output[$a[0]] = e(trim($a[1]));
            }
            return $output;
        }
        if (!is_array($key)) {
            if (is_int($key)) {
                self::status($key);
            } else {
                if (isset($value)) {
                    header($key . ': ' . $value);
                } else {
                    header($key);
                }
            }
        } else {
            foreach ($key as $k => $v) {
                header($k . ': ' . $v);
            }
        }
        return new static;
    }

    public static function is($id = null, $key = null) {
        $r = strtoupper($_SERVER['REQUEST_METHOD']);
        if (isset($id)) {
            $id = strtoupper($id);
            if (isset($key)) {
                return array_key_exists($key, $GLOBALS['_' . $id]);
            }
            return $id === $r;
        }
        return $r;
    }

    public static function type($mime, $charset = null) {
        header('Content-Type: ' . $mime . (isset($charset) ? '; charset=' . $charset : ""));
        return new static;
    }

    // Save state
    public static function save($id, $key = null, $value = null) {
        $data = self::__callStatic($id, [$key, $value]);
        if (isset($key)) {
            if (!is_array($key)) {
                $key = [$key => $value];
            }
        } else {
            $key = $data;
        }
        $s = self::$config['session']['request'] . '.' . $id;
        $cache = Session::get($s, []);
        Session::set($s, array_replace_recursive($cache, $key));
        return new static;
    }

    // Restore state
    public static function restore($id, $key = null, $fail = null) {
        $s = self::$config['session']['request'] . '.' . $id;
        $cache = Session::get($s, []);
        if (isset($key)) {
            self::delete($id, $key);
            return Anemon::get($cache, $key, $fail);
        }
        self::delete($id);
        return $cache;
    }

    // Delete state
    public static function delete($id, $key = null) {
        Session::reset(self::$config['session']['request'] . '.' . $id . (isset($key) ? '.' . $key : ""));
        return new static;
    }

    public static function __callStatic($kin, $lot = []) {
        if (!self::_($kin)) {
            $data = $GLOBALS['_' . strtoupper($kin)];
            $data = isset($data) ? $data : [];
            $key = array_shift($lot);
            $fail = array_shift($lot);
            $eval = array_shift($lot);
            if (!isset($eval)) {
                $eval = true;
            }
            if (isset($key)) {
                $o = Anemon::get($data, $key, $fail);
                $o = $eval ? e($o) : $o;
                return $o === 0 || $o === '0' || !empty($o) ? $o : $fail;
            }
            return !empty($data) ? ($eval ? e($data) : $data) : $fail;
        }
        return parent::__callStatic($kin, $lot);
    }

}