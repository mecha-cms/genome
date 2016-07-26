<?php

class Parse extends DNA {

    protected $fn = [];
    protected $input = "";
    protected $output = "";

    public function from($input, $output = false) {
        $this->input = $input;
        $this->output = $output;
    }

    public function to() {
        $lot = func_get_args();
        $c = static::class;
        $cc = array_shift($lot);
        if (isset($this->fn[$c][$cc])) {
            return call_user_func_array($this->fn[$c][$cc], $cc);
        }
        return $this->output;
    }

}