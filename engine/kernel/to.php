<?php

class To extends DNA {

    protected $fn = [];

    public function safe(...$lot) {
        $c = static::class;
        if (count($lot) === 2 && is_callable($lot[1])) {
            $this->fn[$c][$lot[0]] = $lot[1];
            return true;
        }
        $id = array_shift($lot);
        $input = array_shift($lot);
        if (isset($this->fn[$c][$id])) {
            return call_user_func_array($this->fn[$c][$id], $lot);
        }
        return $input;
    }

}