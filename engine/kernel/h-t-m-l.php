<?php

class HTML extends Union {

    protected function _apart_(string $in = "", $eval = true) {
        $out = parent::_apart_($in, $eval);
        if (!empty($out[2])) {
            foreach ($out[2] as $k => $v) {
                if (strpos($k, 'data-') === 0) {
                    $out[2]['data[]'][substr($k, 5)] = $v;
                    unset($out[2][$k]);
                } else if ($k === 'class') {
                    $out[2]['class[]'] = ($eval && $v === true || !$eval && $v === 'class') ? [] : array_unique(explode(' ', $v));
                    unset($out[2][$k]);
                } else if ($k === 'style') {
                    if ($v !== 'style') {
                        // TODO: preserve `;` inside quote(s)
                        foreach (explode(';', $v) as $vv) {
                            if (trim($vv) === "")
                                continue;
                            $a = explode(':', $vv . ':');
                            if (trim($a[1]) === "")
                                continue;
                            $out[2]['style[]'][trim($a[0])] = e(trim($a[1]));
                        }
                    } else {
                        $out[2]['style[]'] = [];
                    }
                    unset($out[2][$k]);
                }
            }
        }
        return $out;
    }

    // HTML comment
    protected function _comment_(string $content = "", int $dent = 0, $block = false) {
        $dent = self::dent($dent);
        $begin = $end = $block ? N : ' ';
        if ($block) {
            $content = $dent . str_replace(N, N . $dent, $content);
            $end = N . $dent;
        }
        return $dent . '<!--' . $begin . $content . $end . '-->';
    }

    // Build HTML attribute(s)…
    protected function _data_($a = []) {
        if (is_array($a)) {
            foreach ($a as $k => $v) {
                if (!is_array($v))
                    continue;
                // HTML5 `data-*` attribute
                if ($k === 'data[]') {
                    ksort($v);
                    foreach ($v as $kk => $vv) {
                        if (!isset($vv) || $vv === false)
                            continue;
                        $a['data-' . $kk] = fn\is\anemon($vv) ? json_encode($vv) : $vv;
                    }
                    unset($a[$k]);
                // Class value as array
                } else if ($k === 'class[]') {
                    if (isset($a['class'])) {
                        $v = $a['class'] !== true ? concat(explode(' ', $a['class']), $v) : [];
                    }
                    $v = array_filter(array_unique($v));
                    sort($v);
                    $v = implode(' ', $v);
                    $a['class'] = $v !== "" ? $v : null;
                    unset($a[$k]);
                // Inline CSS via `style[]` attribute
                } else if ($k === 'style[]') {
                    $css = "";
                    // ksort($v);
                    foreach ($v as $kk => $vv) {
                        if (!isset($vv) || $vv === false)
                            continue;
                        $css .= $kk . ':' . $vv . ';';
                    }
                    $a['style'] = $css !== "" ? $css : null;
                    unset($a[$k]);
                }
            }
        }
        return parent::_data_($a);
    }

    public function __call(string $kin, array $lot = []) {
        if (!self::_($kin) && !method_exists($this, '_' . $kin . '_')) {
            array_unshift($lot, $kin);
            return $this->_unite_(...$lot);
        }
        return parent::__call($kin, $lot);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (!self::_($kin)) {
            return (new static)->__call($kin, $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}