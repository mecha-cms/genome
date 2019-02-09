<?php

abstract class Union extends Genome {

    const config = [
        'union' => [
            // 0 => [
            //     0 => ['\<', '\>', '\/'],
            //     1 => ['\=', '\"', '\"', '\s']
            // ],
            1 => [
                0 => ['<', '>', '/'],
                1 => ['=', '"', '"', ' ']
            ]
        ],
        'pattern' => [
            0 => ['[\w:.-]+'],
            1 => ['[\w:.-]+', '(?:[^"\\\]|\\\.)*']
        ]
    ];

    public $union = [];
    public $pattern = [];

    protected $unit = [];
    protected $dent = [];

    public static $config = self::config;

    // Build union attribute(s)…
    protected function _data_($a = []) {
        if (is_scalar($a)) {
            $a = trim((string) $a);
            return strlen($a) ? ' ' . $a : "";
        }
        $out = "";
        $u = $this->union[1][1];
        ksort($a);
        foreach ($a as $k => $v) {
            if (!isset($v) || $v === false) {
                continue;
            }
            if (is_array($v) || (is_object($v) && !fn\is\instance($v))) {
                $v = json_encode($v);
            }
            $out .= $u[3] . ($v !== true ? $k . $u[0] . $u[1] . s(self::x($v)) . $u[2] : $k);
        }
        return $out;
    }

    // Base union constructor
    protected function _unite_($unit = null, $content = "", array $data = [], $dent = 0) {
        // `$union->unite(['div', "", ['id' => 'foo']], 1)`
        if (is_array($unit)) {
            $dent = $content ?: 0;
            $unit = extend([
                0 => null,
                1 => "",
                2 => []
            ], $unit);
            $data = $unit[2];
            $content = $unit[1];
            $unit = $unit[0];
        }
        // `$union->unite('div', "", ['id' => 'foo'], 0)`
        $dent = self::dent($dent);
        $u = $this->union[1][0];
        $s = $dent . $u[0] . $unit . $this->_data_($data);
        return $s . ($content === false ? $u[1] : $u[1] . $content . $u[0] . $u[2] . $unit . $u[1]);
    }

    // Inverse version of `Union::unite()`
    protected function _apart_(string $in = "", $eval = true) {
        $u = $this->union[1][0];
        $d = $this->union[1][1];
        $r = $this->pattern;
        $x_u = $this->union[0][0] ?? [];
        $x_d = $this->union[0][1] ?? [];
        $u0 = $x_u[0] ?? x($u[0]); // `<`
        $u1 = $x_u[1] ?? x($u[1]); // `>`
        $u2 = $x_u[2] ?? x($u[2]); // `/`
        $d0 = $x_d[0] ?? x($d[0]); // `=`
        $d1 = $x_d[1] ?? x($d[1]); // `"`
        $d2 = $x_d[2] ?? x($d[2]); // `"`
        $d3 = $x_d[3] ?? x($d[3]); // ` `
        $in = trim($in);
        $out = [
            0 => null, // `Element.nodeName`
            1 => null, // `Element.innerHTML`
            2 => []    // `Element.attributes`
        ];
        $s = '/^' . $u0 . '(' . $r[0][0] . ')(' . $d3 . '.*?)?(?:' . $u2 . $u1 . '|' . $u1 . '(?:([\s\S]*?)(' . $u0 . $u2 . '\1' . $u1 . '))?)$/s';
        // Must starts with `<` and ends with `>`
        if ($u[0] && $u[1] && substr($in, 0, strlen($u[0])) === $u[0] && substr($in, -strlen($u[1])) === $u[1]) {
            // Does not match with pattern, abort!
            if (!preg_match($s, $in, $m)) {
                return false;
            }
            $out[0] = $m[1];
            $out[1] = isset($m[4]) ? $m[3] : false;
            if (!empty($m[2]) && preg_match_all('/' . $d3 . '+(' . $r[1][0] . ')(?:' . $d0 . $d1 . '(' . $r[1][1] . ')' . $d2 . ')?/s', $m[2], $mm)) {
                foreach ($mm[1] as $k => $v) {
                    $s = To::HTML(v($mm[2][$k]));
                    $s = $eval ? e($s) : $s;
                    if ($s === "" && strpos($mm[0][$k], $d[0] . $d[1] . $d[2]) === false) {
                        $s = $eval ? true : $v;
                    }
                    $out[2][$v] = $s;
                }
            }
            return $out;
        }
        return false;
    }

    // Base union unit open
    protected function _begin_($unit = null, array $data = [], $dent = 0) {
        $dent = self::dent($dent);
        $this->unit[] = $unit;
        $this->dent[] = $dent;
        $u = $this->union[1][0];
        return $dent . $u[0] . $unit . $this->_data_($data) . $u[1];
    }

    // Base union unit close
    protected function _end_($unit = null, $dent = null) {
        if ($unit === true) {
            // Close all!
            $out = "";
            while (array_pop($this->unit)) {
                $out .= $this->_end_() . ($dent ?: N);
            }
            return $out;
        }
        $unit = $unit ?? array_pop($this->unit);
        $dent = isset($dent) ? self::dent($dent) : array_pop($this->dent);
        $u = $this->union[1][0];
        return $unit ? $dent . $u[0] . $u[2] . $unit . $u[1] : "";
    }

    // Indent…
    public static function dent($i = 0) {
        return is_numeric($i) ? str_repeat(DENT, (int) $i) : $i;
    }

    // Encode all union’s special character(s)…
    public static function x($v = "") {
        return is_string($v) ? From::HTML($v) : $v;
    }

    public function __construct(array $config = []) {
        $this->union = extend(self::config['union'], static::$config['union'] ?? []);
        $this->pattern = extend(self::config['pattern'], static::$config['pattern'] ?? []);
        if ($config) {
            foreach (['union', 'pattern'] as $k) {
                if (!empty($config[$k])) {
                    $this->{$k} = extend($this->{$k}, $config[$k]);
                }
            }
        }
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