<?php

class Cell extends __ {

    protected static $data = [
        'src' => null,
        'alt' => null,
        'width' => null,
        'height' => null,
        'property' => null,
        'name' => null,
        'content' => null,
        'class' => null,
        'id' => null,
        'href' => null,
        'rel' => null,
        'target' => null,
        'type' => null,
        'action' => null,
        'method' => null,
        'enctype' => null,
        'value' => null,
        'placeholder' => null,
        'label' => null,
        'selected' => null,
        'checked' => null,
        'disabled' => null,
        'readonly' => null,
        'style' => null
    ];

    protected static $unit = [];
    protected static $dent = [];

    // Indent ...
    public static function dent($i) {
        return is_numeric($i) ? str_repeat(I, (int) $i) : $i;
    }

    // Encode all HTML entit(y|ies)
    public static function x($v) {
        if (!is_string($v)) return $v;
        return To::html_x($v);
    }

    // Setup HTML attribute(s) ...
    public static function bond($a, $unit = "") {
        if (!is_array($a)) {
            $data = trim((string) $a);
            return strlen($data) ? ' ' . $data : ""; // no filter(s) applied ...
        }
        $output = "";
        $c = strtolower(static::class);
        $unit = $unit ? '.' . $unit : "";
        $array = Hook::NS($c . ':bond' . $unit, [$unit], array_replace(self::$data, $a));
        // HTML5 `data-*` attribute
        if (isset($a['data']) && is_array($a['data'])) {
            foreach ($a['data'] as $k => $v) {
                if ($v === null) continue;
                $a['data-' . $k] = $v;
            }
            unset($a['data']);
        }
        foreach ($a as $k => $v) {
            if ($v === null) continue;
            if (is_array($v)) {
                // Inline CSS via `style` attribute
                if ($k === 'style') {
                    $css = "";
                    foreach ($v as $kk => $vv) {
                        if ($vv === null) continue;
                        $css .= ' ' . $kk . ': ' . str_replace('"', '&quot;', $vv) . ';';
                    }
                    $v = substr($css, 1);
                } else {
                    $v = implode(' ', array_unique($v));
                }
            }
            $q = is_string($v) && strpos($v, '"') !== false ? "'" : '"';
            $output .= ' ' . ($v !== true ? $k . '=' . $q . $v . $q : $k);
        }
        return $output;
    }

    // Base HTML tag constructor
    public static static function unit($unit = 'html', $content = "", $data = [], $dent = 0) {
        $dent = self::dent($dent);
        $c = strtolower(static::class);
        $s  = $dent . '<' . $unit . self::bond($data, $unit);
        $s .= $content === false ? ES : '>' . ($content ?? "") . '</' . $unit . '>';
        return Hook::NS($c . ':unit.' . $unit, [$data], $s);
    }

    // Alias for `Cell::unit()`
    public static function unite(...$lot) {
        return call_user_func_array('self::unit', $lot);
    }

    // Inverse version of `Cell::unite()`
    public static function apart($input, $unit = [], $data = [], $eval = true) {
        $E = ['<', '>', '/', '[\w:.-]+'];
        $A = ['=', '"', '"', ' ', '[\w:.-]+'];
        $E0 = preg_quote($E[0], '/');
        $E1 = preg_quote($E[1], '/');
        $E2 = preg_quote($E[2], '/');
        $A0 = preg_quote($A[0], '/');
        $A1 = preg_quote($A[1], '/');
        $A2 = preg_quote($A[2], '/');
        $A3 = preg_quote($A[3], '/');
        $output = [
            'unit' => null,
            'data' => [],
            'content' => null
        ];
        if(!preg_match('/^\s*' . $E0 . '(' . $E[3] . ')(?:' . $A3 . '*' . $E2 . '?' . $E1 . '|(' . $A3 . '+.*?)' . $A3 . '*' . $E2 . '?' . $E1 . ')(([\s\S]*?)' . $E0 . $E2 . '\1' . $E1 . ')?\s*$/s', $input, $m)) return false;
        $output['unit'] = $m[1];
        $output['content'] = $m[4] ?? null;
        if (!empty($m[2]) && preg_match_all('/' . $A3 . '+(' . $A[4] . ')(?:' . $A0 . $A1 . '([\s\S]*?)' . $A2 . ')?/s', $m[2], $mm)) {
            foreach ($mm[1] as $k => $v) {
                $s = $eval ? e($mm[2][$k]) : $mm[2][$k];
                if ($s === "" && strpos($mm[0][$k], $A[0] . $A[1] . $A[2]) === false) {
                    $s = $v;
                }
                $output['data'][$v] = $s;
            }
        }
        return $output;
    }

    // HTML comment
    public static function __($content = "", $dent = 0, $block = N) {
        $dent = self::dent($dent);
        $begin = $end = $block;
        if (strpos($block, N) !== false) {
            $end = $block . $dent;
        }
        $c = strtolower(static::class);
        return Hook::NS($c . ':unit.__', [], $dent . '<!--' . $begin . $content . $end . '-->');
    }

    // Base HTML tag open
    public static function begin($unit = 'html', $data = [], $dent = 0) {
        $dent = self::dent($dent);
        self::_unit[] = $unit;
        self::$dent[] = $dent;
        $c = strtolower(static::class);
        return Hook::NS($c . ':begin.' . $unit, [$data], $dent . '<' . $unit . self::bond($data, $unit) . '>');
    }

    // Base HTML tag close
    public static function end($unit = null, $dent = null) {
        $unit = $unit ?? array_pop(self::_unit);
        $dent = $dent ?? array_pop(self::$dent) ?? "";
        $c = strtolower(static::class);
        return Hook::NS($c . ':end.' . $unit, [], $unit ? $dent . '</' . $unit . '>' : "");
    }

    // Calling `Cell::div($x)` is the same as calling `Cell::unit('div', $x)` when
    // custom method called `Cell::div()` is not defined yet by the `Cell::plug()`
    public static function __callStatic($kin, $lot = []) {
        $c = static::class;
        if (!self::kin($kin)) {
            array_unshift($lot, $kin);
            return call_user_func_array('self::unit', $lot);
        }
        $s = parent::__callStatic($kin, $lot);
        return Hook::NS(strtolower($c) . ':gen.' . $kin, [$lot], $s);
    }

}