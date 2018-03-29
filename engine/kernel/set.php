<?php

class Set extends Genome {

    public static function __callStatic($kin, $lot = []) {
        if (!self::_($kin)) {
            $id = '_' . strtoupper($kin);
            $key = array_shift($lot);
            $value = array_shift($lot);
            if (is_array($key)) {
                if (!isset($value)) {
                    Anemon::extend($GLOBALS[$id], $key);
                } else {
                    // TODO
                    if (!extension_loaded('curl')) {
                        exit('<a href="http://php.net/curl" title="PHP &ndash; cURL" rel="nofollow" target="_blank">PHP cURL</a> extension is not installed on your web server.');
                    }
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_HEADER, true);
                    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    if ($kin === 'get') {
                        $value .= To::query($key);
                        curl_setopt($curl, CURLOPT_HTTPGET, true);
                    } else if ($kin === 'post') {
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $key);
                    }
                    curl_setopt($curl, CURLOPT_URL, $value);
                    $output = curl_exec($curl);
                    curl_close($curl);
                    return $output;
                }
            } else {
                Anemon::set($GLOBALS[$id], $key, $value);
            }
            return new static;
        }
        return parent::__callStatic($kin, $lot);
    }

}