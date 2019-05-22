<?php

function shield(string $query) {
    $a = explode(':', $query, 2);
    if (isset($GLOBALS['SHIELD'][1][$query])) {
        return $GLOBALS['SHIELD'][1][$query];
    }
    if (is_file($f = SHIELD . DS . $a[0] . DS . 'index.php')) {
        $out = [];
        if (is_file($f = dirname($f) . DS . 'state' . DS . ($a[1] ?? 'config') . '.php')) {
            extract($GLOBALS, EXTR_SKIP);
            $out = require $f;
        }
        $out = Hook::fire('shield.state.' . strtr($query, '.', '/'), [$out]);
        return ($GLOBALS['SHIELD'][1][$query] = $out);
    }
    return null;
}