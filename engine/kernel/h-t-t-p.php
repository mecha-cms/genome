<?php

final class HTTP extends Genome {

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $id = '_' . strtoupper($kin);
        $data = $GLOBALS[$id] ?? [];
        $key = array_shift($lot);
        $eval = array_shift($lot) ?? true;
        if (isset($key)) {
            $o = Anemon::get($data, $key);
            $o = $eval ? e($o) : $o;
            return $o === 0 || $o === '0' || !empty($o) ? $o : null;
        }
        return $eval ? e($data) : $data;
    }

    // Fetch remote URL
    public static function fetch(string $url, $agent = null) {
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
        $header = array_values($header);
        if (extension_loaded('curl')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_FAILONERROR => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 15
            ]);
            $out = curl_exec($curl);
            if (defined('DEBUG') && DEBUG && $out === false) {
                err(curl_error($curl));
                exit;
            }
            curl_close($curl);
        } else {
            $out = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $header)
                ]
            ]));
        }
        return $out !== false ? $out : null;
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
                if ($value === false) {
                    header_remove($key);
                } else if (isset($value)) {
                    header($key . ': ' . $value);
                } else if ($key === false) {
                    header_remove();
                } else {
                    header($key);
                }
            }
        } else {
            foreach ($key as $k => $v) {
                header($k . ': ' . $v);
            }
        }
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

    public static function query(array $query = null, $c = []) {
        $c = extend(['?', '&amp;', '=', ""], is_array($c) ? $c : [1 => $c], false);
        if (!isset($query)) {
            $query = $GLOBALS['URL']['query'];
            return str_replace(['?', '&', '='], $c, $query);
        }
        return To::query($query ? extend($_GET, (array) $query) : $_GET, $c);
    }

    public static function status(int $i = null) {
        if (is_int($i)) {
            http_response_code($i);
        } else if (!isset($i)) {
            return http_response_code();
        }
    }

    public static function type(string $mime, string $charset = null) {
        header('Content-Type: ' . $mime . (isset($charset) ? '; charset=' . $charset : ""));
    }

}