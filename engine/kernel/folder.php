<?php

class Folder extends File {

    public function __toString() {
        return "";
    }

    public static function create($path, $consent = 0775) {
        $path = (array) $path;
        $consent = (array) $consent;
        foreach ($path as $k => &$v) {
            if (!file_exists($v) || is_file($v)) {
                $c = array_key_exists($k, $consent) ? $consent[$k] : end($consent);
                mkdir($v = To::path($v), $c, true);
            }
        }
        return count($path) === 1 ? end($path) : $path;
    }

    // Alias for `create`
    public static function set($path, $consent = 0755) {
        return static::create($path, $consent);
    }

    public function delete($files = null) {
        if (!isset($files)) {
            return parent::delete();
        }
        $path = $this->path;
        $out = [];
        foreach ((array) $files as $file) {
            parent::open($v = $path . DS . $file)->delete();
            $out[] = $v;
        }
        return $out;
    }

    // Alias for `delete`
    public function reset($files = null) {
        return $this->delete($files);
    }

    public static function exist($path, $fail = false) {
        $path = parent::exist($path);
        return $path && is_dir($path) ? $path : $fail;
    }

    public function size(string $unit = null, $prec = 2) {
        $sizes = 0;
        if ($this->exist) {
            if (!is_dir($this->path))
                return false;
            if (!glob($this->path . DS . '*', GLOB_NOSORT))
                return parent::sizer(0, $unit, $prec);
            foreach (parent::explore([$folder, 1], true, []) as $k => $v) {
                $sizes += filesize($k);
            }
        }
        return parent::sizer($sizes, $unit, $prec);
    }

}