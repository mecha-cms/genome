<?php

class SGML extends Genome implements \ArrayAccess, \Countable, \Serializable {

    const config = [
        0 => [
            0 => ['<!--', '-->']
        ],
        1 => [
            0 => ['<', '>', '/'],
            1 => ['=', '"', '"', ' ']
        ]
    ];

    protected $lot = [
        0 => "", // `Element.nodeName`
        1 => "", // `Element.innerHTML`
        2 => []  // `Element.attributes`
    ];

    public $c = [];
    public $type = 1;

    public static $config = self::config;

    public function __construct(...$lot) {
        $this->type = $type = array_shift($lot) ?? 1;
        $this->c = $c = extend(self::config, static::$config, array_shift($lot) ?? []);
        if (is_string($type) && strlen($type)) {
            $c = $c[$type] ?? [];
            if (strpos($type, $c[0]) === 0 && substr($type, -strlen($c[1])) === $c[1]) {
                // Do apart!
            }
        }
        parent::__construct();
    }

    public function __toString() {
        $c = $this->c[$k = $this->type] ?? [];
        // Comment
        if ($k === 0) {
            return $c[0][0] . ($this->lot[1] ?? "") . $c[0][1];
        // Element
        } else if ($k === 1) {
            $o = $this->lot;
            $out = $c[0][0] . $o[0];
            if (!empty($o[2])) {
                foreach ($o[2] as $k => $v) {
                    if (!is_string($v)) {
                        $v = json_encode($v);
                    }
                    $out .= $c[1][3] . $k . $c[1][0] . $c[1][1] . $v . $c[1][2];
                }
            }
            $out .= ($o[1] === false ? $c[0][2] : $c[0][1] . $o[1] . $c[0][0] . $c[0][2] . $o[0]) . $c[0][1];
            return $out;
        // Text
        } else if ($k === 2) {
            return $this->lot[1];
        }
        return "";
    }

    public function count() {
        return 1; // Single node is always `1`
    }

    public function getIterator() {
        return new \ArrayIterator($this->lot);
    }

    public function offsetExists($i) {
        return isset($this->lot[$i]);
    }

    public function offsetGet($i) {
        return $this->lot[$i] ?? null;
    }

    public function offsetSet($i, $value) {
        if (!isset($i)) {
            $this->lot[] = $value;
        } else {
            $this->lot[$i] = $value;
        }
    }

    public function offsetUnset($i) {
        unset($this->lot[$i]);
    }

    public function serialize() {
        return serialize($this->lot);
    }

    public function unserialize($lot) {
        $this->lot = unserialize($lot);
    }

}