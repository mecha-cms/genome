<?php

class Union extends Genome {

    const config = [
        'union' => [
            // 0 => [
            //     0 => ['\<', '\>', '\/'],
            //     1 => ['\=', '\"', '\"', '\s'],
            //     2 => ['\<\!\-\-', '\-\-\>']
            // ],
            1 => [
                0 => ['<', '>', '/'],
                1 => ['=', '"', '"', ' '],
                2 => ['<!--', '-->']
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
    protected function Genome_data($a) {
        if (!is_array($a)) {
            $a = trim((string) $a);
            return strlen($a) ? ' ' . $a : "";
        }
        $output = "";
        $u = $this->union[1][1];
        ksort($a);
        foreach ($a as $k => $v) {
            if (!isset($v)) continue;
            if (is_array($v) || (is_object($v) && !fn\is\instance($v))) {
                $v = json_encode($v);
            }
            $output .= $u[3] . ($v !== true ? $k . $u[0] . $u[1] . static::x($v) . $u[2] : $k);
        }
        return $output;
    }

    // Base union constructor
    protected function Genome_unite($unit, $content = "", $data = [], $dent = 0) {
        // `$union->unite(['div', "", ['id' => 'foo']], 1)`
        if (is_array($unit)) {
            $dent = $content ?: 0;
            $unit = array_replace([
                0 => null,
                1 => "",
                2 => []
            ], $unit);
            $data = $unit[2];
            $content = $unit[1];
            $unit = $unit[0];
        }
        // `$union->unite('div', "", ['id' => 'foo'], 0)`
        $dent = static::dent($dent);
        $u = $this->union[1][0];
        $s = $dent . $u[0] . $unit . $this->Genome_data($data);
        return $s . ($content === false ? $u[1] : $u[1] . $content . $u[0] . $u[2] . $unit . $u[1]);
    }

    // Inverse version of `Union::unite()`
    protected function Genome_apart($input, $eval = true) {
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
        $input = trim($input);
        $output = [
            0 => null, // `Element.nodeName`
            1 => null, // `Element.innerHTML`
            2 => []    // `Element.attributes`
        ];
        $s = '/^' . $u0 . '(' . $r[0][0] . ')(' . $d3 . '.*?)?(?:' . $u2 . $u1 . '|' . $u1 . '(?:([\s\S]*?)(' . $u0 . $u2 . '\1' . $u1 . '))?)$/s';
        // Must starts with `<` and ends with `>`
        if ($u[0] && $u[1] && substr($input, 0, strlen($u[0])) === $u[0] && substr($input, -strlen($u[1])) === $u[1]) {
            // Does not match with pattern, abort!
            if (!preg_match($s, $input, $m)) {
                return false;
            }
            $output[0] = $m[1];
            $output[1] = isset($m[4]) ? $m[3] : false;
            if (!empty($m[2]) && preg_match_all('/' . $d3 . '+(' . $r[1][0] . ')(?:' . $d0 . $d1 . '(' . $r[1][1] . ')' . $d2 . ')?/s', $m[2], $mm)) {
                foreach ($mm[1] as $k => $v) {
                    $s = To::HTML(v($mm[2][$k]));
                    $s = $eval ? e($s) : $s;
                    if ($s === "" && strpos($mm[0][$k], $d[0] . $d[1] . $d[2]) === false) {
                        $s = $v;
                    }
                    $output[2][$v] = $s;
                }
            }
            return $output;
        }
        return false;
    }

    // Union comment
    protected function Genome___($content = "", $dent = 0, $block = N) {
        $dent = static::dent($dent);
        $begin = $end = $block;
        if (strpos($block, N) !== false) {
            $end = $block . $dent;
        }
        $u = $this->union[1][2];
        return $dent . $u[0] . $begin . $content . $end . $u[1];
    }

    // Base union unit open
    protected function Genome_begin($unit = 'html', $data = [], $dent = 0) {
        $dent = static::dent($dent);
        $this->unit[] = $unit;
        $this->dent[] = $dent;
        $u = $this->union[1][0];
        return $dent . $u[0] . $unit . $this->Genome_data($data) . $u[1];
    }

    // Base union unit close
    protected function Genome_end($unit = null, $dent = null) {
        if ($unit === true) {
            // Close all!
            $s = "";
            while (array_pop($this->unit)) {
                $s .= $this->Genome_end() . ($dent ?: N);
            }
            return $s;
        }
        $unit = $unit ?? array_pop($this->unit);
        $dent = isset($dent) ? static::dent($dent) : array_pop($this->dent);
        $u = $this->union[1][0];
        return $unit ? $dent . $u[0] . $u[2] . $unit . $u[1] : "";
    }

    // Indent…
    public static function dent($i) {
        return is_numeric($i) ? str_repeat(DENT, (int) $i) : $i;
    }

    // Encode all union’s special character(s)…
    public static function x($v) {
        return is_string($v) ? From::HTML($v) : $v;
    }

    public function __construct($config = []) {
        $this->union = static::$config['union'];
        $this->pattern = static::$config['pattern'];
        if ($config) {
            foreach (['union', 'pattern'] as $k) {
                if (!empty($config[$k])) {
                    $this->{$k} = array_replace_recursive($this->{$k}, $config[$k]);
                }
            }
        }
    }

}