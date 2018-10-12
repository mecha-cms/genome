<?php

Shield::_('abort', function($code = 404, $fail = false) {
    $i = is_string($code) ? explode('/', str_replace(DS, '/', $code))[0] : '404';
    HTTP::status((int) $i);
    Shield::attach($code, $fail);
});

Shield::_('exist', function($input, $fail = false) {
    return Folder::exist(SHIELD . DS . $input, $fail);
});

Shield::_('state', function(...$lot) {
    $c = Shield::class;
    $id = basename(array_shift($lot));
    $key = array_shift($lot);
    $fail = array_shift($lot) ?: false;
    $folder = (is_array($key) ? $fail : array_shift($lot)) ?: constant(u($c));
    $state = $folder . DS . $id . DS . 'state' . DS . 'config.php';
    $id = str_replace('.', '/', $id);
    if (!file_exists($state)) {
        return is_array($key) ? $key : $fail;
    }
    $state = isset(self::$state[$c][$id]) ? self::$state[$c][$id] : include $state;
    $state = Hook::fire(c2f($c, '_', '/') . '.state.' . $id, [$state]);
    if (is_array($key)) {
        return array_replace_recursive($key, $state);
    }
    return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
});