<?php

final class HTTP extends Genome {

    private static $header = [];

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $id = '_' . strtoupper($kin);
        $value = $GLOBALS[$id] ?? [];
        $key = array_shift($lot);
        $eval = array_shift($lot) ?? true;
        if (isset($key)) {
            $out = get($value, $key);
            return $eval ? e($out) : $out;
        }
        return $eval ? e($value) : $value;
    }

    // Fetch remote URL
    public static function fetch(string $url, $agent = null) {
        $data = [];
        // <https://tools.ietf.org/html/rfc7231#section-5.5.3>
        $a = 'Mecha/' . Mecha::version . ' (+' . $GLOBALS['URL']['$'] . ')';
        // `HTTP::fetch('/', false, ['Content-Type' => 'text/html'])`
        if (is_array($agent)) {
            foreach ($agent as $k => $v) {
                $data[$k] = $k . ': ' . $v;
            }
        }
        if (!isset($data['User-Agent'])) {
            $data['User-Agent'] = 'User-Agent: ' . $a;
        }
        $data = array_values($data);
        if (extension_loaded('curl')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_FAILONERROR => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => $data,
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 15
            ]);
            $out = curl_exec($curl);
            if (defined('DEBUG') && DEBUG && $out === false) {
                throw new \UnexpectedValueException(curl_error($curl));
            }
            curl_close($curl);
        } else {
            $out = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $data)
                ]
            ]));
        }
        return $out !== false ? $out : null;
    }

    public static function header($key = null, $value = null) {
        if (!isset($key)) {
            $out = [];
            foreach ($_SERVER as $k => $v) {
                if (strpos($k, 'HTTP_') === 0) {
                    $out[preg_replace_callback('/_(\w)/', function($m) {
                        return '-' . strtoupper($m[1]);
                    }, ucfirst(strtolower(substr($k, 5))))] = $v;
                }
            }
            foreach (headers_list() as $v) {
                $a = explode(':', $v, 2);
                $out[$a[0]] = e(trim($a[1]));
            }
            if (function_exists('apache_response_headers')) {
                $out = array_replace(apache_response_headers() ?: [], $out);
            }
            return (self::$header = $out);
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if ($v === false) {
                    header_remove($k);
                    unset(self::$header[$k]);
                } else {
                    header($k . ': ' . $v);
                    self::$header[$k] = $v;
                }
            }
        } else {
            if ($value === false) {
                header_remove($key);
                unset(self::$header[$key]);
            } else if (isset($value)) {
                header($key . ': ' . $value);
                self::$header[$key] = $value;
            } else if ($key === false) {
                header_remove();
                self::$header = [];
            } else {
                return self::$header[$key] ?? self::header()[$key] ?? null;
            }
        }
    }

    public static function is(string $id = null, string $key = null) {
        $r = strtoupper($_SERVER['REQUEST_METHOD']);
        if (isset($id)) {
            $id = strtoupper($id);
            if (isset($key)) {
                return get($GLOBALS['_' . $id], $key) !== null;
            }
            return $id === $r;
        }
        return strtolower($r);
    }

    public static function refresh(int $i) {
        header('Refresh: ' . $i);
    }

    public static function status(int $i = null) {
        if (isset($i)) {
            http_response_code($i);
        }
        return http_response_code();
    }

    public static function type(string $type, array $data = []) {
        $s = "";
        foreach ($data as $k => $v) {
            $s .= '; ' . $k . '=' . $v;
        }
        header('Content-Type: ' . $type . $s);
    }

}