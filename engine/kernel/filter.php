<?php

class Filter extends DNA {

    protected $lot = [];

    public function add($id, $fn, $stack = null) {
        $c = static::class;
        $stack = $stack ?? 10;
        if (is_string($id)) {
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
                $this->add($v, $fn, $stack);
            }
        }
    }

    public function apply($id, $target, $lot = []) {
        if (is_string($id)) {
            $c = static::class;
            if (!isset($this->lot[1][$c][$id])) {
                $this->lot[1][$c][$id] = [];
                return $target;
            }
            $s = Anemon::eat($this->lot[1][$c][$id])->sort('ASC', 'stack')->vomit();
            foreach ($s as $k => $v) {
                $lot[0] = $target;
                $target = call_user_func_array($v['fn'], $lot);
            }
        } else {
            foreach (array_reverse($id) as $v) {
                $lot[0] = $v;
                $lot[1] = $target;
                $target = call_user_func_array([$this, __METHOD__], $lot);
            }
        }
        return $target;
    }

    public function remove($id = null, $stack = null) {
        if (is_string($id)) {
            $c = static::class;
            if ($id !== null) {
                $this->lot[0][$c][$id][$stack ?? 10] = $this->lot[1][$c][$id] ?? 1;
                if (isset($this->lot[1][$c][$id])) {
                    if ($stack !== null) {
                        foreach ($this->lot[1][$c][$id] as $k => $v) {
                            if (
                                // remove filter by function name
                                $v['fn'] === $stack ||
                                // remove filter by function stack
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
                $this->remove($v, $stack);
            }
        }
    }

    public function exist($id = null, $fail = false) {
        $c = static::class;
        if ($id === null) {
            return !empty($this->lot[1][$c]) ? $this->lot[1][$c] : $fail;
        }
        return $this->lot[1][$c][$id] ?? $fail;
    }

    public function removed($id = null, $stack = null, $fail = false) {
        $c = static::class;
        $stack = $stack ?? 10;
        if ($id === null) {
            return !empty($this->lot[0][$c]) ? $this->lot[0][$c] : $fail;
        } elseif ($stack === null) {
            return !empty($this->lot[0][$c][$id]) ? $this->lot[0][$c][$id] : $fail;
        }
        return $this->lot[0][$c][$id][$stack] ?? $fail;
    }

    public function NS(...$lot) {
        if(strpos($lot[0], ':') !== false) {
            $s = explode(':', $lot[0], 2);
            $lot[0] = [$lot[0], $s[1]];
        }
        return call_user_func_array([$this, 'apply'], $lot);
    }

}