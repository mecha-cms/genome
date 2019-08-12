<?php

final class Language extends Config {

    public function __call(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $out = self::get($kin = p2f($kin), ...$lot);
        if (is_array($out)) {
            if ($lot) {
                $i = $lot[0] ?? 1;
                if (is_int($i)) {
                    return sprintf($out[$i] ?? $out[$i > 1 ? 2 : 1] ?? $kin, $i);
                }
                return $out[1] ?? $out[0] ?? $kin;
            }
            return $out[1] ?? $out[0] ?? o($out);
        }
        // Asynchronous value with function closure
        if ($out instanceof \Closure) {
            return fire($out, $lot, $this, static::class) ?? $kin;
        }
        // Rich asynchronous value with class instance
        if (is_callable($out) && !is_string($out)) {
            return call_user_func($out, ...$lot) ?? $kin;
        }
        // Else, static value
        return (string) ($out ?? $kin);
    }

    public function __get(string $key) {
        return $this->__call($key);
    }

    public function __invoke(...$v) {
        return count($v) < 2 ? self::get(...$v) : self::set(...$v);
    }

    public function __isset(string $key) {
        return $this->__call($key) !== p2f($key);
    }

    public function __unset(string $key) {
        unset(self::$lot[static::class][p2f($key)]);
    }

    public static function get($key = null, $vars = [], $preserve_case = false) {
        $v = self::$lot[$c = static::class] ?? [];
        if (isset($key)) {
            $v = get($v, $key);
            $vars = array_replace([""], (array) $vars);
            if (is_string($v)) {
                if (!$preserve_case && strpos($v, '%') !== 0 && !ctype_upper($vars[0])) {
                    $vars[0] = l($vars[0] ?? "");
                }
                return trim(sprintf($v, ...$vars));
            }
            return o($v) ?? $key;
        }
        return o($v);
    }

}