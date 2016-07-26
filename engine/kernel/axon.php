<?php

// Many types of neuron possess an axon, a protoplasmic protrusion that can extend to distant parts of the body and make thousands of synaptic contacts.

class Axon extends DNA {

    protected $lot = [];

    public function send($id, $fn, $stack = null) {
        $c = static::class;
        $stack = $stack ?? 10;
        if (!is_array($id)) {
            if (!isset($this->lot[0][$c][$id][$stack])) {
                if (!isset($this->lot[1][$c][$id])) {
                    $this->lot[1][$c][$id] = [];
                }
                $this->lot[1][$c][$id][] = [
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        } else {
            foreach ($id as $v) {
                $this->send($v, $fn, $stack);
            }
        }
    }

    public function react($id, $lot = []) {
        if (!is_array($id)) {
            $c = static::class;
            if (isset($this->lot[1][$c][$id])) {
                $signal = Anemon::eat($this->lot[1][$c][$id])->sort('ASC', 'stack')->vomit();
                foreach ($signal as $v) {
                    call_user_func_array($v['fn'], $lot);
                }
            } else {
                $this->lot[1][$c][$id] = [];
            }
        } else {
            $lot = func_get_args();
            foreach ($id as $v) {
                $lot[0] = $v;
                call_user_func_array([$this, 'react'], $lot);
            }
        }
    }

    public function block($id = null, $stack = null) {
        if (!is_array($id)) {
            $c = static::class;
            if ($id !== null) {
                $this->lot[0][$c][$id][$stack ?? 10] = $this->lot[1][$c][$id] ?? 1;
                if (isset($this->lot[1][$c][$id])) {
                    if ($stack !== null) {
                        foreach ($this->lot[1][$c][$id] as $k => $v) {
                            if (
                                // eject weapon by function name
                                $v['fn'] === $stack ||
                                // eject weapon by function stack
                                is_numeric($stack) && $v['stack'] === (float) $stack
                            ) {
                                unset($this->lot[1][$c][$id][$k]);
                            }
                        }
                    } else {
                        unset($this->lot[1][$c][$id]);
                    }
                }
            } else {
                $this->lot[1][$c] = [];
            }
        } else {
            foreach ($id as $v) {
                $this->block($v, $stack);
            }
        }
    }

    public function sent($id = null, $fail = false) {
        $c = static::class;
        if ($id !== null) {
            return $this->lot[1][$c][$id] ?? $fail;
        }
        return !empty($this->lot[1][$c]) ? $this->lot[1][$c] : $fail;
    }

    public function blocked($id = null, $stack = null, $fail = false) {
        $c = static::class;
        $stack = $stack ?? 10;
        if ($id === null) {
            return !empty($this->lot[0][$c]) ? $this->lot[0][$c] : $fail;
        } elseif ($stack === null) {
            return !empty($this->lot[0][$c][$id]) ? $this->lot[0][$c][$id] : $fail;
        }
        return $this->lot[0][$c][$id][$stack] ?? $fail;
    }

}