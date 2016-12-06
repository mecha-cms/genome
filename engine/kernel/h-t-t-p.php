<?php

class HTTP extends Genome {

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
        418 => 'I\'m a teapot', // RFC2324
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

    protected static function status_static($i = 200, $v = null) {
        if (is_int($i) && isset(self::$message[$i])) {
            if (strpos(PHP_SAPI, 'cgi') !== false) {
                header('Status: ' . $i . ' ' . self::$message[$i]);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' ' . $i . ' ' . self::$message[$i]);
            }
        }
        return new static;
    }

    protected static function query_static(...$lot) {
        $q = array_shift($lot);
        $v = array_shift($lot);
        if ($q === null) return __url__('query');
        if (count($lot) === 2) {
            $q = [$q => $v];
        }
        $q = !empty($q) ? array_replace_recursive($_GET, $q) : $_GET;
        $output = [];
        foreach (self::_q($q, "") as $k => $v) {
            if ($v === false) continue;
            $v = $v !== true ? '=' . urlencode(s($v)) : "";
            $output[] = $k . $v;
        }
        return !empty($output) ? '?' . implode('&', $output) : "";
    }

    protected static function _q($a, $k) {
        $output = [];
        $s = $k ? '%5D' : "";
        foreach ($a as $kk => $vv) {
            if (is_array($vv)) {
                $output = array_merge($output, self::_q($vv, $k . $kk . $s . '%5B'));
            } else {
                $output[$k . $kk . $s] = $vv;
            }
        }
        return $output;
    }

    protected static function header_static($k, $v = null) {
        if (!is_array($k)) {
            if (is_int($k)) {
                self::status_static($k);
            } else {
                if ($v !== null) {
                    header($k . ': ' . $v);
                } else {
                    header($k);
                }
            }
        } else {
            foreach ($k as $kk => $vv) {
                header($kk . ': ' . $vv);
            }
        }
        return new static;
    }

    protected static function mime_static($mime, $charset = null) {
        header('Content-Type: ' . $mime . ($charset !== null ? '; charset=' . $charset : ""));
        return new static;
    }

    protected static function post_static($url, $fields = []) {
        if (!function_exists('curl_init')) {
            exit('<a href="http://php.net/curl" title="PHP &ndash; cURL" rel="nofollow" target="_blank">PHP cURL</a> extension is not installed on your web server.');
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    protected static function get_static($url, $fields = []) {
        if (is_string($fields)) {
            $url .= '?' . str_replace([X . '?', X], "", X . $fields);
        } else {
            $url .= '?' . http_build_query($fields);
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        } else {
            return file_get_contents($url);
        }
    }

}