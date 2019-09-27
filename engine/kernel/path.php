<?php

final class Path extends Genome {

    public static function B(string $path, int $step = 1, string $s = DS) {
        if ($s === DS || $s === '/') {
            if ($step === 1) {
                return $path !== "" ? basename($path) : null;
            }
        }
        $path = str_replace([DS, '/'], $s, $path);
        $path = rtrim(implode($s, array_slice(explode($s, $path), $step * -1)), $s);
        return $path !== "" ? $path : null;
    }

    public static function D(string $path, int $step = 1, string $s = DS) {
        if ($s === DS || $s === '/') {
            $dir = rtrim(dirname($path, $step), $s);
            return $dir !== '.' ? $dir : null;
        }
        $path = str_replace([DS, '/'], $s, $path);
        $a = explode($s, $path);
        $path = rtrim(implode($s, array_slice($a, 0, count($a) - $step)), $s);
        return $path !== "" ? $path : null;
    }

    public static function F(string $path, string $s = DS) {
        $f = pathinfo($path, PATHINFO_DIRNAME);
        $n = pathinfo($path, PATHINFO_FILENAME);
        if ($n === "") {
            $n = pathinfo($path, PATHINFO_BASENAME);
        }
        return str_replace([DS, '/'], $s, $f === '.' ? $n : $f . DS . $n);
    }

    public static function N(string $path, $x = false) {
        return (string) pathinfo($path, $x ? PATHINFO_BASENAME : PATHINFO_FILENAME);
    }

    public static function R(string $path, string $root = ROOT, string $s = DS) {
        $root = str_replace([DS, '/'], $s, $root);
        return str_replace([DS, '/', $root . $s, $root], [$s, $s, "", ""], $path);
    }

    public static function X(string $path) {
        if (strpos($path, '.') === false) {
            return null;
        }
        $x = pathinfo($path, PATHINFO_EXTENSION);
        return $x ? strtolower($x) : null;
    }

}