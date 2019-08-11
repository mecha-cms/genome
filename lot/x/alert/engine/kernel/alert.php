<?php

final class Alert extends Genome implements \Countable, \IteratorAggregate, \JsonSerializable, \Serializable {

    private static function t(array $lot, string $kin) {
        $out = [];
        foreach ($lot as $v) {
            $text = $v[0] ?? "";
            $t = rtrim($k = 'alert-' . $kin . '-' . $text, '-');
            $vars = $v[1] ?? [];
            $preserve_case = !empty($v[2]);
            $m = Language::get($t, $vars, $preserve_case);
            $out[] = ['alert', $m !== $k ? $m : $text, ['type' => $kin]];
        }
        return $out;
    }

    public function __toString() {
        if ($alert = self::get()) {
            $out = "";
            foreach ($alert as $v) {
                $out .= new SGML($v);
            }
            return $out;
        }
        return "";
    }

    public function count() {
        return count(self::get());
    }

    public function getIterator() {
        return new \ArrayIterator(self::get() ?? []);
    }

    public function jsonSerialize() {
        return self::get();
    }

    public function serialize() {
        return serialize(self::get());
    }

    public function unserialize($v) {
        foreach ((array) (unserialize($v) ?? []) as $v) {
            self::set($v[2]['type'], $v[1]);
        }
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        array_unshift($lot, $kin);
        return self::set(...$lot);
    }

    public static function get($kin = null) {
        if (is_array($kin)) {
            $out = [];
            foreach ($kin as $v) {
                if (isset($_SESSION['alert'][$v])) {
                    $out = array_merge($out, self::t($_SESSION['alert'][$v], $v));
                    unset($_SESSION['alert'][$v]);
                }
            }
            return $out;
        }
        if (isset($kin) && isset($_SESSION['alert'][$kin])) {
            $out = self::t($_SESSION['alert'][$kin], $kin);
            unset($_SESSION['alert'][$kin]);
            return $out;
        }
        if (isset($_SESSION['alert'])) {
            $out = [];
            foreach ((array) $_SESSION['alert'] as $k => $v) {
                $out = array_merge($out, self::t($v, $k));
                unset($_SESSION['alert'][$k]);
            }
            return $out;
        }
        return null;
    }

    public static function set(...$lot) {
        $_SESSION['alert'][array_shift($lot)][] = $lot;
    }

    public static function let($kin = null) {
        if (is_array($kin)) {
            foreach ($kin as $v) {
                unset($_SESSION['alert'][$v]);
            }
        } else if (isset($kin)) {
            unset($_SESSION['alert'][$kin]);
        } else {
            unset($_SESSION['alert']);
        }
    }

}