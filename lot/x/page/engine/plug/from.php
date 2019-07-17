<?php

From::_('page', function(string $in, $key = null, $eval = false) {
    if (strpos($in = n($in), "---\n") !== 0) {
        // Add empty header
        $in = "---\n...\n\n" . $in;
    }
    $v = static::YAML($in, '  ', true, $eval);
    $v = $v[0] + ['content' => $v["\t"] ?? ""];
    return isset($key) ? (array_key_exists($key, $v) ? $v[$key] : null) : $v;
});