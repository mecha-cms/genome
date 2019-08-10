<?php

Get::_('IP', $IP = function() {
    $for = 'HTTP_X_FORWARDED_FOR';
    if (array_key_exists($for, $_SERVER) && !empty($_SERVER[$for])) {
        if (strpos($_SERVER[$for], ',') > 0) {
            $ip = trim(strstr($ip[0], ',', true));
        } else {
            $ip = $_SERVER[$for];
        }
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
});

Get::_('UA', $UA = function() {
    return $_SERVER['HTTP_USER_AGENT'] ?? null;
});

// Alias(es)…
Get::_('ip', $IP);
Get::_('ua', $UA);