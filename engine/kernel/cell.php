<?php

class Cell extends __ {

    protected $_data = [
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

    protected $_unit = [];
    protected $_dent = [];

    // Indent ...
    public function dent($i) {
        return !is_numeric($i) ? $i : str_repeat(I, (int) $i);
    }

    // Encode all HTML entit(y|ies)
    public function protect($v) {
        if (!is_string($v)) return $v;
        return Text::parse($v)->to('html.encode');
    }

    // Setup HTML attribute(s) ...
    public static function bond($a, $unit = "") {
        if (!is_array($a)) {
            $data = trim((string) $a);
            return strlen($data) ? ' ' . $data : ""; // no filter(s) applied ...
        }
        $output = "";
        $c = strtolower(get_called_class());
        $unit = $unit ? '.' . $unit : "";
        $array = Filter::apply($c . '.bond' . $unit, array_replace($this->_data, $a), [$unit]);
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
                    $v = array_unique($v);
                    sort($v);
                    $v = implode(' ', $v);
                }
            }
            $q = is_string($v) && strpos($v, '"') !== false ? "'" : '"';
            $output .= ' ' . ($v !== true ? $k . '=' . $q . $v . $q : $k);
        }
        return $output;
    }

    // Base HTML tag constructor
    public function unit($unit = 'html', $content = "", $data = [], $dent = 0) {
        $dent = $this->dent($dent);
        $c = strtolower(get_called_class());
        $s  = $dent . '<' . $unit . $this->bond($data, $unit);
        $s .= $content === false ? ES : '>' . ($content ?? "") . '</' . $unit . '>';
        return Filter::apply($c . ':unit.' . $unit, $s, [$data]);
    }

    // Alias for `Cell::unit()`
    public function unite() {
        return call_user_func_array([$this, 'unit'], func_get_args());
    }

    // Inverse version of `Cell::unit()`
    public function apart($input, $unit = [], $data = [], $eval = true) {
        $E = ['<', '>', '/', '[\w:.-]+'];
        $A = ['=', '"', '"', ' ', '[\w:.-]+'];
        $E0 = preg_quote($E[0], '/');
        $E1 = preg_quote($E[1], '/');
        $E2 = preg_quote($E[2], '/');
        $A0 = preg_quote($A[0], '/');
        $A1 = preg_quote($A[1], '/');
        $A2 = preg_quote($A[2], '/');
        $A3 = preg_quote($A[3], '/');
        $results = [
            'unit' => null,
            'data' => [],
            'content' => null
        ];
        if(!preg_match('/^\s*' . $E0 . '(' . $E[3] . ')(?:' . $A3 . '*' . $E2 . '?' . $E1 . '|(' . $A3 . '+.*?)' . $A3 . '*' . $E2 . '?' . $E1 . ')(([\s\S]*?)' . $E0 . $E2 . '\1' . $E1 . ')?\s*$/s', $input, $m)) return false;
        $results['unit'] = $m[1];
        $results['content'] = $m[4] ?? null;
        if (!empty($m[2]) && preg_match_all('/' . $A3 . '+(' . $A[4] . ')(?:' . $A0 . $A1 . '([\s\S]*?)' . $A2 . ')?/s', $m[2], $mm)) {
            foreach ($mm[1] as $k => $v) {
                $s = $eval ? Converter::strEval($mm[2][$k]) : $mm[2][$k];
                if ($s === "" && strpos($mm[0][$k], $A[0] . $A[1] . $A[2]) === false) {
                    $s = $v;
                }
                $results['data'][$v] = $s;
            }
        }
        return $results;
    }

    // HTML comment
    public function __($content = "", $dent = 0, $block = N) {
        $dent = $this->dent($dent);
        $begin = $end = $block;
        if (strpos($block, N) !== false) {
            $end = $block . $dent;
        }
        $c = strtolower(get_called_class());
        return Filter::apply($c . '.unit.__', $dent . '<!--' . $begin . $content . $end . '-->');
    }

    // Base HTML tag open
    public function begin($unit = 'html', $data = [], $dent = 0) {
        $dent = $this->dent($dent);
        $this->_unit[] = $unit;
        $this->_dent[] = $dent;
        $c = strtolower(get_called_class());
        return Filter::apply($c . '.begin.' . $unit, $dent . '<' . $unit . $this->bond($data, $unit) . '>', $data);
    }

    // Base HTML tag close
    public function end($unit = null, $dent = null) {
        $unit = $unit ?? array_pop($this->_unit);
        $dent = $dent ?? array_pop($this->_dent) ?? "";
        $c = strtolower(get_called_class());
        return Filter::apply($c . '.end.' . $unit, $unit ? $dent . '</' . $unit . '>' : "");
    }

    // Add new method with `Cell::add('foo')`
    public function add($kin, $fn) {
        $this->plug($kin, $fn);
    }

    // Call the added method with `Cell::foo()`
    public function __call($kin, $lot = []) {
        $c = get_called_class();
        if (!isset($this->_[$c][$kin])) {
            $lot = array_merge([$kin], $lot);
            return call_user_func_array([$this, 'unit'], $lot);
        }
        $s = parent::__call($kin, $lot);
        return Filter::apply(strtolower($c) . '.gen.' . $kin, $s, $lot);
    }

}