<?php

class Blob extends File {

    protected $blob;

    public $exist;
    public $path;

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        return $this->blob[$kin] ?? null;
    }

    public function __construct(array $file) {
        $this->exist = false;
        $this->blob = $file;
        $this->path = $file['tmp_name'] ?: null;
    }

    public function __toString() {
        if (is_file($blob = $this->path)) {
            return file_get_contents($blob);
        }
        return "";
    }

    public function _name($x = false) {
        $name = basename($this->path);
        return $x === true ? $name : pathinfo($name, PATHINFO_FILENAME) . (is_string($x) ? '.' . $x : "");
    }

    public function _size() {
        return $this->blob['size'] ?? null;
    }

    public function _x() {
        if (!$this->error()) {
            $name = basename($this->path);
            if (strpos($name, '.') === false) {
                return null;
            }
            $x = pathinfo($name, PATHINFO_EXTENSION);
            return $x ? strtolower($x) : null;
        }
        return null;
    }

    public function error() {
        return $this->blob['error'] ?? null;
    }

    public function name($x = false) {
        $name = $this->blob['name'];
        return $x === true ? $name : pathinfo($name, PATHINFO_FILENAME) . (is_string($x) ? '.' . $x : "");
    }

    public function saveAs(string $name, $consent = null) {
        // Save to the ground
        return $this->saveTo(GROUND . DS . basename($name), $consent);
    }

    public function saveTo(string $path, $consent = null) {
        $path = strtr($path, '/', DS);
        if (!is_dir($d = dirname($path))) {
            mkdir($d, 0775, true);
        }
        if (is_file($path)) {
            return false; // File already exists
        }
        if (move_uploaded_file($this->path, $path) !== false) {
            if (isset($consent)) {
                chmod($path, $consent);
            }
            return $path; // Return `$path` on success
        }
        return null; // Return `null` on error
    }

    public function size(string $unit = null, $prec = 2) {
        if (!$this->error()) {
            return parent::sizer($this->blob['size'], $unit, $prec);
        }
        return null;
    }

    public function type() {
        return $this->blob['type'] ?? null;
    }

    public function x() {
        if (!$this->error()) {
            $name = $this->blob['name'];
            if (strpos($name, '.') === false) {
                return null;
            }
            $x = pathinfo($name, PATHINFO_EXTENSION);
            return $x ? strtolower($x) : null;
        }
        return null;
    }

}