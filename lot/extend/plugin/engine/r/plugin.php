<?php

function plugin(string $query) {
    $a = explode(':', $query, 2);
    if (isset($GLOBALS['PLUGIN'][1][$query])) {
        return $GLOBALS['PLUGIN'][1][$query];
    }
    if (is_file($f = PLUGIN . DS . $a[0] . DS . 'index.php')) {
        $out = [];
        if (is_file($f = dirname($f) . DS . 'lot' . DS . 'state' . DS . ($a[1] ?? 'config') . '.php')) {
            extract($GLOBALS, EXTR_SKIP);
            $out = require $f;
        }
        $out = Hook::fire('plugin.state.' . strtr($query, '.', '/'), [$out]);
        return ($GLOBALS['PLUGIN'][1][$query] = $out);
    }
    return null;
}