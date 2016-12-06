<?php

class Color extends \Genome {

    public $color = "";
    public $k = null;

    public function __construct($input, $kind = null) {
        $this->color = $input;
        $this->k = $kind ?? $this->parse(['kind' => null])['kind'];
    }

    public function parse($fail = false) {
        $s = $this->color;
        // `#aabbcc` or `#abc`
        if (strpos($s, '#') === 0 && preg_match('#^\#([a-f\d]{3}|[a-f\d]{6})$#i', $s, $m)) {
            $m = str_split(l(strlen($m[1]) === 3 ? preg_replace('#.#', '$0$0', $m[1]) : $m[1]), 2);
            return [
                '__color' => array_merge($m, [1]),
                'color' => [hexdec($m[0]) / 255, hexdec($m[1]) / 255, hexdec($m[2]) / 255, 1], // 255 >= a, b, c >= 0
                'kind' => 'hex'
            ];
        // `rgb(255, 255, 255)` or `rgba(255, 255, 255, .4)`
        } elseif (stripos($s, 'rgb') === 0 && preg_match('#^(rgba?)\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:\s*,\s*((?:0?\.)?\d+))?\s*\)$#i', $s, $m)) {
            return [
                '__color' => [(int) $m[2], (int) $m[3], (int) $m[4], (float) ($m[5] ?? 1)],
                'color' => [$m[2] / 255, $m[3] / 255, $m[4] / 255, (float) ($m[5] ?? 1)], // 255 >= a, b, c >= 0
                'kind' => l($m[1])
            ];
        // `hsv(0, 10%, 10%)` or `hsva(0, 10%, 10%, .4)`
        } elseif (stripos($s, 'hsv') === 0 && preg_match('#^(hsva?)\s*\(\s*(\d+)\s*,\s*(\d+)%\s*,\s*(\d+)%\s*(?:\s*,\s*((?:0?\.)?\d+))?\s*\)$#i', $s, $m)) {
            return [
                '__color' => [(int) $m[2], (int) $m[3], (int) $m[4], (float) ($m[5] ?? 1)],
                'color' => [$m[2] / 360, $m[3] / 100, $m[4] / 100, (float) ($m[5] ?? 1)], // 360 >= a >= 0; 100 >= b >= 0; 100 >= c >= 0; 1 >= d => 0;
                'kind' => l($m[1])
            ];
        }
        return $fail;
    }

}