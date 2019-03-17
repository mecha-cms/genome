<?php

// Alias for `$config`
Lot::set('site', $site = $config);
// Prepare current shield state
Lot::set('state', $state = new State);

// Alias for `Config`
class_alias('Config', 'Site');

// Include worker(s)…
require __DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker' . DS . 'config.php';
require __DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker' . DS . 'hook.php';

// Load current shield state if any
$folder = SHIELD . DS . Shield::$config['id'] . DS;
if ($f = File::exist($folder . 'state' . DS . 'config.php')) {
    Lot::set('state', $state = new State($f));
}

// Load user language(s) from the current shield folder if any
$i18n = $folder . 'language' . DS;
if ($l = File::exist([
    $i18n . $config->language . '.page',
    $i18n . 'en-us.page'
])) {
    $i18n = new Page($l, [], ['*', 'language']);
    $fn = 'From::' . $i18n->type;
    $c = $i18n->content;
    Language::set(is_callable($fn) ? call_user_func($fn, $c) : (array) $c);
}

// Run shield task if any
if ($task = File::exist($folder . 'task.php')) {
    include $task;
}

// Load user function(s) from the current shield folder if any
if ($fn = File::exist($folder . 'index.php')) {
    call_user_func(function() use($fn) {
        extract(Lot::get(), EXTR_SKIP);
        require $fn;
    });
}

// Detect relative asset path to the `.\lot\shield\*` folder
if (Extend::exist('asset') && $assets = Asset::get()) {
    foreach ($assets as $k => $v) {
        foreach ($v as $kk => $vv) {
            // Full path, no change!
            if (
                strpos($kk, ROOT) === 0 ||
                strpos($kk, '//') === 0 ||
                strpos($kk, '://') !== false
            ) {
                continue;
            }
            // Relative to the `asset` folder of current shield
            if ($path = Asset::path($folder . 'asset' . DS . $kk)) {
                Asset::reset($kk);
                Asset::set($path, $vv['stack']);
            }
        }
    }
}