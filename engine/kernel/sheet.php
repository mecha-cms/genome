<?php

class Sheet extends __ {

    public static $sv = ['====', ': ', "\n"];
    public static $sx = ['&#61;&#61;&#61;&#61;', '&#58;&#32;', '&#10;'];

    protected $open = "";
    protected $anemon = [];
    protected $anemon_body = 'content';

    // Escape ...
    public function x($s) {
        return str_replace(self::$sv, self::$sx, $s);
    }

    // Un-Escape ...
    public function v($s) {
        return str_replace(self::$sx, self::$sv, $s);
    }

    // Explode ...
    public function apart($input = null) {
        $input = $input ?? $this->open;
        // By file path or by file content?
        $input = X . n(strpos($input, ROOT) === 0 ? file_get_contents($input) : $input);
        $sheet = str_replace([X . self::$sv[0] . N, X], "", $sheet);
        $sheet = explode(N . self::$sv[0] . N, $sheet, 2);
        // Do header ...
        foreach (explode(N, trim($sheet[0])) as $v) {
            $s = explode(self::$sv[1], $v, 2);
            $this->anemon[0][$this->v(trim($s[0]))] = $this->v(trim($s[1] ?? 'false'));
        }
        // Do content ...
        $this->anemon[1] = $this->v(trim($sheet[1] ?? ""));
        return $this->anemon;
    }

    // Implode ...
    public function unite($input = null) {
        $input = $input ?? $this->anemon;
        $input[1] = $input[$this->anemon_body] ?? $input[1] ?? "";
        unset($input[$this->anemon_body]);
        $input[0] = $input[0] ?? $input;
        $output = self::$sv[0];
        foreach ($input[0] as $k => $v) {
            $v = Is::anemon($v) ? To::json($v) : s($v);
            $output .= N . '@' . $k . self::$sv[1] . $v;
        }
        $output .= N . self::$sv[0] . N . N;
        $output .= $input[1];
        return $output;
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
            // Remove that `@` prefix
            $k = substr($k, 1);
            $v = Hook::NS($NS . '__' . $k, [$lot], $v);
            $output['__' . $k] = $v;
            $v = Hook::NS($NS . 'speck.input', [$lot], $v); // before speck set-up
            $v = Hook::NS($NS . $k, [$lot], $v); // public item
            $v = Hook::NS($NS . 'speck.output', [$lot], $v); // after speck set-up
            $output[$k] = $v;
        }
        // Set sheet content ...
        $v = Hook::NS($NS . '__' . $this->anemon_body, [$lot], $lot[1] ?? "");
        $v = Hook::NS($NS . 'speck.input', [$lot], $v);
        $v = Hook::NS($NS . $this->anemon_body, [$lot], $v);
        $v = Hook::NS($NS . 'speck.output', [$lot], $v);
        $output[$this->anemon_body] = $v;
        return $output;
    }

    public function data($data, $body = 'content') {
        $sheet = new self;
        $sheet->anemon_body = $body;
        foreach ($data as $k => $v) {
            $sheet->anemon[$sheet->x($k)] = $sheet->x($v);
        }
        return $sheet;
    }

    public function open($path, $body = 'content') {
        $sheet = new self;
        $sheet->open = $path;
        $sheet->anemon_body = $body;
        return $sheet;
    }

    public function saveTo($path, $consent = 0600) {
        File::open($path)->write($this->unite($this->anemon))->save($consent);
    }

    public function save($consent = 0600) {
        return $this->saveTo($this->open, $consent);
    }

}