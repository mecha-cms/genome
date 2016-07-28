<?php

class Is extends DNA {

    protected $bucket = [];

    // Initialize ...
    public function this($input) {
        $this->bucket = $input;
        return $this;
    }

    // @ditto
    public function these(...$input) {
        $this->bucket = count((array) $input) === 1 ? (array) a($input) : $input;
        return $this;
    }

    // Check if `$this->bucket` contains `$s`
    public function has($s, $all = false, $x = X) {
        $input = $x . implode($x . $this->bucket) . $x;
        if (is_array($s)) {
            if (!$all) {
                foreach ($s as $v) {
                    if (strpos($input, $x . $v . $x) !== false) {
                        return true;
                    }
                }
                return false;
            } else {
                $pass = 0;
                foreach ($s as $v) {
                    if (strpos($input, $x . $v . $x) !== false) {
                        $pass++;
                    }
                }
                return $pass === count($s);
            }
        }
        return strpos($input, $x . $s . $x) !== false;
    }

    // Check for empty string, array or object
    public function void($x) {
        return (
            $x === "" ||
            is_string($x) && trim($x) === "" ||
            is_array($x) && empty($x) ||
            is_object($x) && empty((array) $x)
        );
    }

    // Check for IP address
    public function ip($x) {
        return filter_var($x, FILTER_VALIDATE_IP);
    }

    // Check for URL address
    public function url($x) {
        return filter_var($x, FILTER_VALIDATE_URL);
    }

    // Check for email address
    public function email($x) {
        return filter_var($x, FILTER_VALIDATE_EMAIL);
    }

    // Check for valid boolean value
    public function toggle($x) {
        return filter_var($x, FILTER_VALIDATE_BOOLEAN);
    }

    // Is equal to `$x`
    public function eq($x) {
        return q($this->bucket) === $x;
    }

    // Is less than `$x`
    public function lt($x) {
        return q($this->bucket) < $x;
    }

    // Is greater than `$x`
    public function gt($x) {
        return q($this->bucket) > $x;
    }

    // Is less than or equal to `$x`
    public function lte($x) {
        return q($this->bucket) <= $x;
    }

    // Is greater than or equal to `$x`
    public function gte($x) {
        return q($this->bucket) >= $x;
    }

}