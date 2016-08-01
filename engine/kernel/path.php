<?php

class Path extends __ {

    public function B($path, $step = 1, $s = DS) {
        if (($s !== DS && $s !== '/') || $step > 1) {
            $p = explode($s, $path);
            return implode($s, array_slice($p, $step * -1));
        }
        return basename($path);
    }

    public function D($path, $step = 1, $s = DS) {
        if (($s !== DS && $s !== '/') || $step > 1) {
            $p = explode($s, $path);
            for ($i = 0; $i < $step; ++$i) {
                array_pop($p);
            }
            return implode($s, $p);
        }
        return dirname($path) === '.' ? "" : dirname($path);
    }

    public function N($path, $x = false) {
        return pathinfo($path, $x ? PATHINFO_BASENAME : PATHINFO_FILENAME);
    }

    public function X($path, $fail = false) {
        if (strpos($path, '.') === false) return $fail;
        $x = pathinfo($path, PATHINFO_EXTENSION);
        return $x ? strtolower($x) : $fail;
    }

}