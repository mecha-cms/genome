<?php

class Sheet extends Socket {

    public static $v = [
	    "---\n",
		"\n...",
		': ',
		"\n"
	];

	public static $x = [
	    '&#x2d;&#x2d;&#x2d;&#xa;',
		'&#xa;&#x2e;&#x2e;&#x2e;',
		'&#x3a;&#x20;',
		'&#xa;'
	];

	protected $c = "";
    protected $a = [];
    protected $open = "";

	public function __construct($c = 'content') {
		$this->c = $c;
		return $this;
	}

	public static function _(...$lot) {
		return new self($lot[0]);
	}

    public static function open($path, $c = 'content') {
        $sheet = self::_($c);
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
		$input = strpos($input, ROOT) === 0 ? file_get_contents($input) : $input;
		$input = str_replace([X . self::$v[0], X], "", $input . N . N);
		$input = explode(self::$v[1] . N . N, $input, 2);
		// Do header ...
		foreach ($input[0] as $v) {
			$v = explode(self::$v[2], $v, 2);
			$this->a[self::v($v[0])] = self::v($v[1] ?? 'false');
		}
		// Do content ...
		$this->a[$this->c] = trim($input[1] ?? "");
		return $this->a;
	}

	// Unite ...
	public function unite($input = null, $c = null) {
		$input = $input ?? $this->a;
		$c = $c ?? $this->c;
		$cc = $input[$c] ?? "";
		$ccc = [];
		unset($input[$c]);
		foreach ($input as $k => $v) {
			$ccc[] = self::x($k) . self::$v[2] . self::x($v);
		}
		return self::$v[0] . implode(N, $ccc) . self::$v[1] . ($cc ? N . N . $cc : "");
	}

	// Create ...
    public static function data($data, $c = 'content') {
        $sheet = self::_($c);
        foreach ($data as $k => $v) {
            $sheet->a[self::x($k)] = self::x($v);
        }
        return $sheet;
    }

    public function read($output = [], $NS = 'sheet:', $lot = []) {
        $lot = $this->apart($this->open);
        // Pre-defined sheet data ...
        if ($output) {
            foreach ($output as $k => $v) {
                $v = Hook::NS($NS . '__' . $k, [$lot], $v);
                $output['__' . $k] = $v; // private item
                $v = Hook::NS($NS . 'speck.input', [$lot], $v); // before speck set-up
                $v = Hook::NS($NS . $k, [$lot], $v); // public item
                $v = Hook::NS($NS . 'speck.output', [$lot], $v); // after speck set-up
                $output[$k] = $v;
            }
        }
        // Load sheet data ...
        foreach ($lot[0] as $k => $v) {
            $v = Hook::NS($NS . '__' . $k, [$lot], $v);
            $output['__' . $k] = $v;
            $v = Hook::NS($NS . 'speck.input', [$lot], $v); // before speck set-up
            $v = Hook::NS($NS . $k, [$lot], $v); // public item
            $v = Hook::NS($NS . 'speck.output', [$lot], $v); // after speck set-up
            $output[$k] = $v;
        }
        // Set sheet content ...
        $v = Hook::NS($NS . '__' . $this->a_body, [$lot], $lot[1] ?? "");
        $v = Hook::NS($NS . 'speck.input', [$lot], $v);
        $v = Hook::NS($NS . $this->a_body, [$lot], $v);
        $v = Hook::NS($NS . 'speck.output', [$lot], $v);
        $output[$this->a_body] = $v;
        return $output;
    }

    public function saveTo($path, $consent = 0600) {
        File::open($path)->write($this->unite())->save($consent);
    }

    public function save($consent = 0600) {
        return $this->saveTo($this->open, $consent);
    }

}