<?php

class Blob extends File {

    private $lot;

    public $exist;
    public $path;

    public function __call(string $kin, array $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        return $this->lot[$kin] ?? null;
    }

    public function __construct(array $file) {
        $this->exist = false;
        $this->lot = $file;
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
        return $this->lot['size'] ?? null;
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
        return $this->lot['error'] ?? null;
    }

    public function name($x = false) {
        $name = $this->lot['name'];
        return $x === true ? $name : pathinfo($name, PATHINFO_FILENAME) . (is_string($x) ? '.' . $x : "");
    }

    public function save() {}
    public function saveAs() {}
    public function saveTo() {}

    /*
    public static function pull(string $path, string $name = null, string $type = null) {
        if (is_file($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . ($type ?? 'application/octet-stream'));
            header('Content-Disposition: attachment; filename="' . ($name ?? basename($path)) . '"');
            header('Content-Length: ' . filesize($path));
            header('Expires: 0');
            header('Pragma: public');
            readfile($path);
            exit;
        }
        return false; // Return `false` if file does not exist
    }
    public static function push(array $blob, string $path) {
        $path = rtrim(strtr($path, '/', DS), DS);
        if (!empty($blob['error'])) {
            return $blob['error']; // Has error, abort!
        }
        if (is_file($f = $path . DS . $blob['name'])) {
            return false; // File already exists
        }
        // Destination folder does not exist
        if (!is_dir($path)) {
            mkdir($path, 0775, true); // Create one!
        }
        move_uploaded_file($blob['tmp_name'], $f);
        return $f; // There is no error, the file uploaded with success
    }
    */

    public function size(string $unit = null, $prec = 2) {
        if (!$this->error()) {
            return parent::sizer($this->lot['size'], $unit, $prec);
        }
        return null;
    }

    public function type() {
        return $this->lot['type'] ?? null;
    }

    public function x() {
        if (!$this->error()) {
            $name = $this->lot['name'];
            if (strpos($name, '.') === false) {
                return null;
            }
            $x = pathinfo($name, PATHINFO_EXTENSION);
            return $x ? strtolower($x) : null;
        }
        return null;
    }

}