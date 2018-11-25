<?php

Shield::_('exist', function(string $id) {
    return file_exists(SHIELD . DS . $id . DS . 'index.php');
});

Shield::_('state', function(...$lot) {
    $c = Shield::class;
    $id = basename(array_shift($lot));
    $key = array_shift($lot);
    $fail = array_shift($lot) ?: false;
    $folder = (is_array($key) ? $fail : array_shift($lot)) ?: constant(u($c));
    $state = $folder . DS . $id . DS . 'state' . DS . 'config.php';
    $id = strtr($id, '.', '/');
    if (!file_exists($state)) {
        return is_array($key) ? $key : $fail;
    }
    $state = isset(self::$state[$c][$id]) ? self::$state[$c][$id] : include $state;
    $state = Hook::fire(c2f($c, '_', '/') . '.state.' . $id, [$state]);
    if (is_array($key)) {
        return extend($key, $state);
    }
    return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
});