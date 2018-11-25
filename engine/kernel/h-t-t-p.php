<?php

class HTTP extends Genome {

    const config = [
        'session' => [
            'gate' => '136c8c95'
        ]
    ];

    public static $config = self::config;

    public static $message = [

        // Information Response(s)
        100 => 'Continue',
        101 => 'Switching Protocol',
        102 => 'Processing', // RFC2518

        // Successful Response(s)
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

        // Redirection Message(s)
        300 => 'Multiple Choice',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => null, // Reserved!
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect', // RFC-reschke-http-status-308-07

        // Client Error Response(s)
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
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I’m a Teapot', // RFC2324
        422 => 'Unprocessable Entity', // RFC4918
        423 => 'Locked', // RFC4918
        424 => 'Failed Dependency', // RFC4918
        425 => null, // Reserved for WebDAV advanced collections expired proposal (RFC2817)
        426 => 'Upgrade Required', // RFC2817
        428 => 'Precondition Required', // RFC6585
        429 => 'Too Many Requests', // RFC6585
        431 => 'Request Header Fields Too Large', // RFC6585
        451 => 'Unavailable For Legal Reasons',

        // Server Error Response(s)
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

    public static function status(int $i = null) {
        if (is_numeric($i) && isset(self::$message[$i])) {
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

    public static function query(array $query = null, $c = []) {
        $c = extend(['?', '&amp;', '=', ""], is_array($c) ? $c : [1 => $c], false);
        if (!isset($query)) {
            $query = $GLOBALS['URL']['query'];
            return str_replace(['?', '&', '='], $c, $query);
        }
        return To::query($query ? extend($_GET, (array) $query) : $_GET, $c);
    }

    public static function header($key = null, $value = null) {
        if (!isset($key)) {
            $out = [];
            foreach (headers_list() as $v) {
                $a = explode(':', $v, 2);
                $out[$a[0]] = e(trim($a[1]));
            }
            return $out;
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

    public static function is(string $id = null, string $key = null) {
        $r = strtoupper($_SERVER['REQUEST_METHOD']);
        if (isset($id)) {
            $id = strtoupper($id);
            if (isset($key)) {
                return Anemon::get($GLOBALS['_' . $id], $key, X) !== X;
            }
            return $id === $r;
        }
        return strtolower($r);
    }

    public static function type(string $mime, string $charset = null) {
        header('Content-Type: ' . $mime . (isset($charset) ? '; charset=' . $charset : ""));
        return new static;
    }

    // Save state
    public static function save($key = null, $value = null) {
        $data = $_POST ?? [];
        if (isset($key)) {
            if (!is_array($key)) {
                $key = [$key => $value];
            }
        } else {
            $key = $data;
        }
        $id = self::$config['session']['gate'];
        $cache = Session::get($id, []);
        Session::set($id, extend($cache, $key));
        return new static;
    }

    // Restore state
    public static function restore($key = null, $fail = null) {
        $id = self::$config['session']['gate'];
        $cache = Session::get($id, []);
        if (isset($key)) {
            self::delete($key);
            return Anemon::get($cache, $key, $fail);
        }
        self::delete($id);
        return $cache;
    }

    // Delete state
    public static function delete($key = null) {
        Session::reset(self::$config['session']['gate'] . (isset($key) ? '.' . $key : ""));
        return new static;
    }

    // Fetch remote URL
    public static function fetch($url, $fail = false, string $agent = null) {
        $agent = $agent ?? 'Mecha/' . Mecha::version . ' (+' . $GLOBALS['URL']['$'] . ')';
        if (extension_loaded('curl')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_FAILONERROR => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_USERAGENT => $agent
            ]);
            $out = curl_exec($curl);
            curl_close($curl);
        } else {
            $out = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => 'GET',
                    // <https://tools.ietf.org/html/rfc7231#section-5.5.3>
                    'header' => 'User-Agent: ' . $agent
                ]
            ]));
        }
        return $out !== false ? $out : $fail;
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (!self::_($kin)) {
            $id = '_' . strtoupper($kin);
            $data = $GLOBALS[$id] ?? [];
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