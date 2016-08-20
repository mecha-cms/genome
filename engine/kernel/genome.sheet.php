<?php namespace Genome;

class Sheet extends \Genome {

    public static $v = ["---\n", "\n...", ': ', '- ', "\n"];
    public static $x = ['&#45;&#45;&#45;&#10;', '&#10;&#46;&#46;&#46;', '&#58;&#32;', '&#45;&#32;', '&#10;'];

    protected $meta = [];
    protected $data = "";
    protected $open = "";

    public function __construct($meta = [], $data = "") {
        $this->meta = $meta;
        $this->data = $data;
    }

    public static function open($path) {
        $sheet = new self;
        $sheet->open = $path;
        return $sheet;
    }

    // Escape ...
    public static function x($s) {
        return str_replace(self::$v, self::$x, $s);
    }

    // Un-Escape ...
    public static function v($s) {
        return str_replace(self::$x, self::$v, $s);
    }

    // Apart ...
    public function apart() {
        $input = n(file_get_contents($this->open));
        $input = str_replace([X . self::$v[0], X], "", X . $input . N . N);
        $input = explode(self::$v[1] . N . N, $input, 2);
        // Do meta ...
        foreach (explode(self::$v[4], $input[0]) as $v) {
            $v = explode(self::$v[2], $v, 2);
            $this->meta[self::v($v[0])] = e(self::v($v[1] ?? false));
        }
        // Do data ...
        $this->data = trim($input[1] ?? "");
        return $this;
    }

    // Unite ...
    public function unite() {
        $meta = [];
        foreach ($this->meta as $k => $v) {
            $meta[] = self::x($k) . self::$v[2] . self::x(s($v));
        }
        return self::$v[0] . implode(N, $meta) . self::$v[1] . ($this->data ? N . N . $this->data : "");
    }

    // Create meta ...
    public function meta($a) {
        Anemon::extend($this->meta, $a);
        foreach ($this->meta as $k => $v) {
            if ($v === false) unset($this->meta[$k]);
        }
        return $this;
    }

    // Create data ...
    public function data($s) {
        $this->data = $s;
        return $this;
    }

    public function read($as = 'content', $output = [], $NS = 'sheet:', $lot = []) {
        $a = $this->apart();
        $lot = array_merge($lot, $a->meta);
        // Pre-defined sheet meta ...
        if ($output) {
            foreach ($output as $k => $v) {
                if (strpos($k, '__') !== 0 && !array_key_exists('__' . $k, $output)) {
                    $output['__' . $k] = $v;
                }
            }
        }
        // Load sheet meta ...
        return $this->_meta(array_merge($output, $lot, [$as => $this->data]), $NS, $lot);
    }

    protected function _meta($input, $NS, $lot) {
        $output = [];
        foreach ($input as $k => $v) {
            $v = \Hook::NS($NS . '__' . $k, [$v, $lot]);
            $output['__' . $k] = $v; // private item
            $v = \Hook::NS($NS . 'var.i', [$v, $lot]); // before var set-up
            $v = \Hook::NS($NS . $k, [$v, $lot]); // public item
            $v = \Hook::NS($NS . 'var.o', [$v, $lot]); // after var set-up
            $output[$k] = $v;
        }
        return $output;
    }

    public function saveTo($path, $consent = 0600) {
        \File::open($path)->write($this->unite())->save($consent);
    }

    public function save($consent = 0600) {
        return $this->saveTo($this->open, $consent);
    }

}