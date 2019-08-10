<?php

class Files extends Anemon {

    public function getIterator() {
        $files = [];
        foreach ($this->value as $v) {
            $files[] = $this->file($v);
        }
        return new \ArrayIterator($files);
    }

    public function file(string $path): \ArrayAccess {
        return new File($path);
    }

    public function sort($sort = 1, $preserve_key = false) {
        if (is_array($sort)) {
            $value = [];
            foreach ($this->value as $v) {
                $value[$v] = $this->file($v)[$sort[1]];
            }
            $sort[0] === -1 ? arsort($value) : asort($value);
            $this->value = array_keys($value);
        } else {
            $value = $this->value;
            if ($preserve_key) {
                $sort === -1 ? arsort($value) : asort($value);
            } else {
                $sort === -1 ? rsort($value) : sort($value);
            }
            $this->value = $value;
        }
        return $this;
    }

    public static function from(...$lot) {
        $folder = array_shift($lot);
        $x = array_shift($lot);
        $deep = array_shift($lot) ?? 0;
        $files = [];
        foreach (g($folder, $x, $deep) as $k => $v) {
            if (pathinfo($k, PATHINFO_FILENAME) === "") {
                continue;
            }
            $files[] = $k;
        }
        return new static($files);
    }

}