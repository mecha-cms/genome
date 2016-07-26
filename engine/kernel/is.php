<?php

class Is extends DNA {

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

}