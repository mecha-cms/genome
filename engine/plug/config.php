<?php

Config::plug('i18n', function($key = null, $lot = []) {
    if ($key === null) return Config::get('__i18n', []);
    return vsprintf(Config::get('__i18n.' . $key, $key), $lot + [""]);
});

Config::plug('url', function($key = 'url', $fail = false) {
    return Config::get($key !== true ? 'url.' . $key : 'url', $fail);
});