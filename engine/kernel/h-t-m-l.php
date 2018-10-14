<?php

class HTML extends Union {

    public static $config = self::config;

    // Build HTML attribute(s)…
    protected function Genome_data($a = []) {
        if (is_array($a)) {
            foreach ($a as $k => $v) {
                if (!is_array($v)) continue;
                // HTML5 `data-*` attribute
                if ($k === 'data[]') {
                    ksort($v);
                    foreach ($v as $kk => $vv) {
                        if (!isset($vv)) continue;
                        $a['data-' . $kk] = fn\is\anemon($vv) ? json_encode($vv) : $vv;
                    }
                    unset($a[$k]);
                // Class value as array
                } else if ($k === 'class[]') {
                    if (isset($a['class'])) {
                        $v = concat(explode(' ', $a['class']), $v);
                    }
                    $v = is(array_unique($v));
                    sort($v);
                    $v = implode(' ', $v);
                    $a['class'] = $v !== "" ? $v : null;
                    unset($a[$k]);
                // Inline CSS via `style[]` attribute
                } else if ($k === 'style[]') {
                    $css = "";
                    // ksort($v);
                    foreach ($v as $kk => $vv) {
                        if (!isset($vv)) continue;
                        $css .= $kk . ':' . $vv . ';';
                    }
                    $a['style'] = $css !== "" ? $css : null;
                    unset($a[$k]);
                }
            }
        }
        return parent::Genome_data($a);
    }

    protected function Genome_apart(string $in = "", $eval = true) {
        $out = parent::Genome_apart($in, $eval);
        if (!empty($out[2])) {
            foreach ($out[2] as $k => $v) {
                if (strpos($k, 'data-') === 0) {
                    $out[2]['data[]'][substr($k, 5)] = $v;
                    unset($out[2][$k]);
                } else if ($k === 'class') {
                    $out[2]['class[]'] = $v === 'class' ? [] : array_unique(explode(' ', $v));
                    unset($out[2][$k]);
                } else if ($k === 'style') {
                    if ($v !== 'style') {
                        // TODO: preserve `;` inside quote(s)
                        foreach (explode(';', $v) as $vv) {
                            if (trim($vv) === "") continue;
                            $a = explode(':', $vv . ':');
                            if (trim($a[1]) === "") continue;
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

    public function __call($kin, $lot = []) {
        if (!self::_($kin) && !method_exists($this, 'Genome_' . $kin)) {
            return $this->Genome_unite(...$lot);
        }
        return parent::__call($kin, $lot);
    }

    public static function __callStatic($kin, $lot = []) {
        $that = new static;
        if (!self::_($kin) && !method_exists($that, 'Genome_' . $kin)) {
            array_unshift($lot, $kin);
            return $that->__call($kin, $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}