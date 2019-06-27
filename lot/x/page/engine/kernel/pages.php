<?php

class Pages extends Anemon {

    public function getIterator() {
        $pages = [];
        foreach ($this->value as $v) {
            $pages[] = new Page($v);
        }
        return new \ArrayIterator($pages);
    }

    public function pluck(string $key, $or = null) {
        $value = [];
        foreach ($this->value as $v) {
            $value[] = (new Page($v))[$key] ?? $or;
        }
        return $value;
    }

    public function sort($sort = 1, $preserve_key = false) {
        if (is_array($sort)) {
            $value = [];
            foreach ($this->value as $v) {
                $value[$v] = (new Page($v))[$sort[1]];
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