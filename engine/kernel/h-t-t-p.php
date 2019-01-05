<?php

class HTTP extends Genome {

    const config = [
        'session' => [
            'gate' => '136c8c95'
        ]
    ];

    public static $config = self::config;

    public static function status(int $i = null) {
        if (is_int($i)) {
            http_response_code($i);
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
        $id = static::$config['session']['gate'];
        $cache = Session::get($id, []);
        Session::set($id, extend($cache, $key));
        return new static;
    }

    // Restore state
    public static function restore($key = null, $fail = null) {
        $id = static::$config['session']['gate'];
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
        Session::reset(static::$config['session']['gate'] . (isset($key) ? '.' . $key : ""));
        return new static;
    }

    // Fetch remote URL
    public static function fetch($url, $fail = false, $agent = null) {
        $header = [];
        // <https://tools.ietf.org/html/rfc7231#section-5.5.3>
        $a = 'Mecha/' . Mecha::version . ' (+' . $GLOBALS['URL']['$'] . ')';
        // `HTTP::fetch('/', false, ['Content-Type' => 'text/html'])`
        if (is_array($agent)) {
            foreach ($agent as $k => $v) {
                $header[$k] = $k . ': ' . $v;
            }
        }
        if (!isset($header['User-Agent'])) {
            $header['User-Agent'] = 'User-Agent: ' . $a;
        }
        if (extension_loaded('curl')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_FAILONERROR => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                // CURLOPT_USERAGENT => $a,
                CURLOPT_HTTPHEADER => array_values($header)
            ]);
            $out = curl_exec($curl);
            if (defined('DEBUG') && DEBUG && $out === false) {
                echo fail(curl_error($curl));
                exit;
            }
            curl_close($curl);
        } else {
            $out = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", array_values($header))
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