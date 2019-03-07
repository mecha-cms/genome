<?php

class SGML extends Genome implements \ArrayAccess, \Countable, \JsonSerializable, \Serializable {

    const config = [
        0 => ['<', '>', '/'],
        1 => ['"', '"', '=']
    ];

    protected $lot = [
        0 => "",
        1 => "",
        2 => []
    ];

    public $c;
    public $strict = true;

    public static $config = self::config;

    public function __construct($in = []) {
        $this->c = $c = extend(self::config, static::$config);
        if (is_array($in)) {
            $this->lot = extend($this->lot, $in);
        } else if (is_string($in)) {
            // Must starts with `<` and ends with `>`
            if (strpos($in, $c[0][0]) === 0 && substr($in, -strlen($c[0][1])) === $c[0][1]) {
                $tag = x(implode("", $c[0]));
                $tag_open = x($c[0][0]); // `<`
                $tag_close = x($c[0][1]); // `>`
                $tag_end = x($c[0][2]); // `/`
                $attr = x(implode("", $c[1]));
                $attr_open = x($c[1][2] . $c[1][0]);
                $attr_close = x($c[1][1]);
                if (preg_match('/' . $tag_open . '([^' . $tag . $attr . '\s]+)(\s[^' . $tag_close . ']*)?(?:' . $tag_close . '((?:[\s\S](?!' . $tag_open . '\1(?:\s|' . $tag_end . '|' . $tag_close . ')))*?)(?:' . $tag_open . $tag_end . '(\1)' . $tag_close . ')|(?:' . $tag_end . ')' . ($this->strict ? "" : '?') . $tag_close . ')/', n($in), $m)) {
                    $this->lot = [
                        0 => $m[1],
                        1 => isset($m[4]) ? $m[3] : false,
                        2 => []
                    ];
                    $this->strict = substr($in, -strlen($end = $tag_end . $tag_close)) === $end;
                    if (isset($m[2]) && preg_match_all('/\s+([^' . $attr . '\s]+)(' . $attr_open . '((?:[^' . x($c[1][0] . $c[1][1]) . '\\\]|\\\.)*)' . $attr_close . ')?/', $m[2], $mm)) {
                        if (!empty($mm[1])) {
                            foreach ($mm[1] as $k => $v) {
                                $this->lot[2][$v] = $mm[2][$k] === "" ? true : strtr($mm[3][$k], [
                                    "\\" . $c[1][0] => $c[1][0],
                                    "\\" . $c[1][1] => $c[1][1]
                                ]);
                            }
                        }
                    }
                } else {
                    throw new \ParseError(static::class . ' parsing error: ' . $in);
                }
            } else {
                throw new \ParseError(static::class . ' parsing error: ' . $in);
            }
        }
        parent::__construct();
    }

    public function __toString() {
        $c = $this->c;
        $o = $this->lot;
        $out = $c[0][0] . $o[0];
        if (!empty($o[2])) {
            ksort($o[2]);
            foreach ($o[2] as $k => $v) {
                if ($v === true) {
                    $out .=  ' ' . $k;
                    continue;
                }
                if (!isset($v) || $v === false) {
                    continue;
                }
                $v = strtr($v, [
                    $c[1][0] => "\\" . $c[1][0],
                    $c[1][1] => "\\" . $c[1][1]
                ]);
                $out .= ' ' . $k . $c[1][2] . $c[1][0] . $v . $c[1][1];
            }
        }
        $out .= ($o[1] === false ? ($this->strict ? $c[0][2] : "") : $c[0][1] . $o[1] . $c[0][0] . $c[0][2] . $o[0]) . $c[0][1];
        return $out;
    }

    public function count() {
        return 1; // Single node is always `1`
    }

    public function getIterator() {
        return new \ArrayIterator($this->lot);
    }

    public function jsonSerialize() {
        return $this->lot;
    }

    public function offsetExists($i) {
        if (is_numeric($i)) {
            return isset($this->lot[$i]);
        }
        return isset($this->lot[2][$i]);
    }

    public function offsetGet($i) {
        if (is_numeric($i)) {
            return $this->lot[$i] ?? null;
        }
        return $this->lot[2][$i] ?? null;
    }

    public function offsetSet($i, $value) {
        if (isset($i)) {
            if (is_numeric($i)) {
                $this->lot[$i] = $value;
            } else {
                $this->lot[2][$i] = $value;
            }
        } else {
            $this->lot[] = $value;
        }
    }

    public function offsetUnset($i) {
        if (is_numeric($i)) {
            unset($this->lot[$i]);
        } else {
            unset($this->lot[2][$i]);
        }
    }

    public function serialize() {
        return serialize($this->lot);
    }

    public function unserialize($lot) {
        $this->lot = unserialize($lot);
    }

}