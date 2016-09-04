<?php namespace Seed;

class Union extends \Genome {

    protected $union = [
        'unit' => ['<', '>', '/', '[\w:.-]+'],
        'data' => ['=', '"', '"', ' ', '[\w:.-]+'],
        '.' => ['<!--', '-->']
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

    public function __construct($unit = [], $data = [], $__ = []) {
        \Anemon::extend($this->union, [
            'unit' => $unit,
            'data' => $data,
            '.' => $__
        ]);
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
        return \To::html_encode($v);
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
        $array = \Hook::NS($c . ':bond' . $unit, [array_replace($this->data, $a), $unit]);
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
    public function unite($unit = 'html', $content = "", $data = [], $dent = 0) {
        $dent = $this->dent($dent);
        $c = strtolower(static::class);
        $u = $this->union['unit'];
        $s  = $dent . $u[0] . $unit . $this->bond($data, $unit);
        $s .= $content === false ? $u[1] : $u[1] . ($content ?? "") . $u[0] . $u[2] . $unit . $u[1];
        return \Hook::NS($c . ':unit.' . $unit, [$s, $data]);
    }

    // Inverse version of `Union::unite()`
    public function apart($input, $eval = true) {
        $u = $this->union['unit'];
        $d = $this->union['data'];
        $u0 = x($u[0]);
        $u1 = x($u[1]);
        $u2 = x($u[2]);
        $d0 = x($d[0]);
        $d1 = x($d[1]);
        $d2 = x($d[2]);
        $d3 = x($d[3]);
        $output = [
            'unit' => null,
            'data' => [],
            'content' => null
        ];
        if (!preg_match('/^\s*' . $u0 . '(' . $u[3] . ')(?:' . $d3 . '*' . $u2 . '?' . $u1 . '|(' . $d3 . '+.*?)' . $d3 . '*' . $u2 . '?' . $u1 . ')(([\s\S]*?)' . $u0 . $u2 . '\1' . $u1 . ')?\s*$/s', $input, $m)) return false;
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
        $u = $this->union['.'];
        return \Hook::NS($c . ':unit.__', $dent . $u[0] . $begin . $content . $end . $u[1]);
    }

    // Base union tag open
    public function open($unit = 'html', $data = [], $dent = 0) {
        $dent = $this->dent($dent);
        $this->unit[] = $unit;
        $this->dent[] = $dent;
        $u = $this->union['unit'];
        $c = strtolower(static::class);
        return \Hook::NS($c . ':open.' . $unit, [$dent . $u[0] . $unit . $this->bond($data, $unit) . $u[1], $data]);
    }

    // Base union tag close
    public function close($unit = null, $dent = null) {
        if ($unit === true) {
            // close all
            $s = "";
            foreach ($this->unit as $u) {
                $s .= $this->close() . ($dent ?? N);
            }
            return $s;
        }
        $unit = $unit ?? array_pop($this->unit);
        $dent = $dent ?? array_pop($this->dent) ?? "";
        $c = strtolower(static::class);
        $u = $this->union['unit'];
        return \Hook::NS($c . ':close.' . $unit, $unit ? $dent . $u[0] . $u[2] . $unit . $u[1] : "");
    }

    // ...
    public function __call($kin, $lot) {
        if (!self::kin($kin)) {
            array_unshift($lot, $kin);
            return call_user_func_array([$this, 'unite'], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}