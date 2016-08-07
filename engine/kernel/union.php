<?php

class Union extends Socket {

    protected $union = [
        'unit' => ['<', '>', '/', '[\w:.-]+'],
        'data' => ['=', '"', '"', ' ', '[\w:.-]+'],
        '__' => ['<!--', '-->']
    ];

    protected $data = [
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

    public function __construct($unit = [], $data = []) {
        Anemon::extend($this->union, [
            'unit' => $unit,
            'data' => $data
        ]);
        return $this;
    }

    protected $unit = [];
    protected $dent = [];

    // Indent ...
    public function dent($i) {
        return is_numeric($i) ? str_repeat(I, (int) $i) : $i;
    }

    // Encode all union special character(s)
    public function x($v) {
        if (!is_string($v)) return $v;
        return To::html_x($v);
    }

    // Build union attribute(s) ...
    public function bond($a, $unit = "") {
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

    // Base union constructor
    public function unit($unit = 'html', $content = "", $data = [], $dent = 0) {
        $dent = $this->dent($dent);
        $c = strtolower(static::class);
        $u = $this->union['unit'];
        $s  = $dent . $u[0] . $unit . $this->bond($data, $unit);
        $s .= $content === false ? $u[1] : $u[1] . ($content ?? "") . $u[0] . $u[2] . $unit . $u[1];
        return Hook::NS($c . ':unit.' . $unit, [$data], $s);
    }

    // Alias for `Cell::unit()`
    public function unite(...$lot) {
        return call_user_func_array([$this, 'unit'], $lot);
    }

    // Inverse version of `Cell::unite()`
    public function apart($input, $eval = true) {
        $u = $this->union['unit'];
        $d = $this->union['data'];
        $u0 = preg_quote($u[0], '/');
        $u1 = preg_quote($u[1], '/');
        $u2 = preg_quote($u[2], '/');
        $d0 = preg_quote($d[0], '/');
        $d1 = preg_quote($d[1], '/');
        $d2 = preg_quote($d[2], '/');
        $d3 = preg_quote($d[3], '/');
        $output = [
            'unit' => null,
            'data' => [],
            'content' => null
        ];
        if(!preg_match('/^\s*' . $u0 . '(' . $u[3] . ')(?:' . $d3 . '*' . $u2 . '?' . $u1 . '|(' . $d3 . '+.*?)' . $d3 . '*' . $u2 . '?' . $u1 . ')(([\s\S]*?)' . $u0 . $u2 . '\1' . $u1 . ')?\s*$/s', $input, $m)) return false;
        $output['unit'] = $m[1];
        $output['content'] = $m[4] ?? null;
        if (!empty($m[2]) && preg_match_all('/' . $d3 . '+(' . $d[4] . ')(?:' . $d0 . $d1 . '([\s\S]*?)' . $d2 . ')?/s', $m[2], $mm)) {
            foreach ($mm[1] as $k => $v) {
                $s = $eval ? e($mm[2][$k]) : $mm[2][$k];
                if ($s === "" && strpos($mm[0][$k], $d[0] . $d[1] . $d[2]) === false) {
                    $s = $v;
                }
                $output['data'][$v] = $s;
            }
        }
        return $output;
    }

    // Union comment
    public function __($content = "", $dent = 0, $block = N) {
        $dent = $this->dent($dent);
        $begin = $end = $block;
        if (strpos($block, N) !== false) {
            $end = $block . $dent;
        }
        $c = strtolower(static::class);
        $u = $this->union['__'];
        return Hook::NS($c . ':unit.__', [], $dent . $u[0] . $begin . $content . $end . $u[1]);
    }

    // Base union tag open
    public function open($unit = 'html', $data = [], $dent = 0) {
        $dent = $this->dent($dent);
        $this->unit[] = $unit;
        $this->dent[] = $dent;
        $u = $this->union['unit'];
        $c = strtolower(static::class);
        return Hook::NS($c . ':open.' . $unit, [$data], $dent . $u[0] . $unit . $this->bond($data, $unit) . $u[1]);
    }

    // Base union tag close
    public function close($unit = null, $dent = null) {
        $unit = $unit ?? array_pop($this->unit);
        $dent = $dent ?? array_pop($this->dent) ?? "";
        $c = strtolower(static::class);
        $u = $this->union['unit'];
        return Hook::NS($c . ':close.' . $unit, [], $unit ? $dent . $u[0] . $u[2] . $unit . $u[1] : "");
    }

    // Calling `Union::div($x)` is the same as calling `Union::unit('div', $x)` when
    // custom method called `Union::div()` is not defined yet by the `Union::plug()`
    public static function __callStatic($kin, $lot = []) {
        $c = static::class;
        if (!self::kin($kin)) {
            array_unshift($lot, $kin);
            $union = new $c;
            return call_user_func_array([$c, 'unit'], $lot);
        }
        $s = parent::__callStatic($kin, $lot);
        return Hook::NS(strtolower($c) . ':gen.' . $kin, [$lot], $s);
    }

}