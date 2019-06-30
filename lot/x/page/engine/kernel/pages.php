<?php

class Pages extends Anemon {

    public function getIterator() {
        $pages = [];
        foreach ($this->value as $v) {
            $pages[] = $this->page($v);
        }
        return new \ArrayIterator($pages);
    }

    public function page(string $path) {
        return new Page($path);
    }

    public function sort($sort = 1, $preserve_key = false) {
        if (is_array($sort)) {
            $value = [];
            foreach ($this->value as $v) {
                $value[$v] = $this->page($v)[$sort[1]];
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

}