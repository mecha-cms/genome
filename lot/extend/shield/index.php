<?php

// Include worker(s)…
r(['config', 'hook'], __DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker', Lot::get());

// Alias for `$config`
Lot::set('site', $config);

// Alias for `Config`
class_alias('Config', 'Site');

Hook::set('on.ready', function() {

    // Include global variable(s)…
    extract(Lot::get());

    // Load user language(s) from the current shield folder if any
    $folder = SHIELD . DS . $config->shield . DS;
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

    // Load current shield state if any
    if ($state = File::exist($folder . 'state' . DS . 'config.php')) {
        Lot::set('state', new State($state));
    }

    // Run shield task if any
    if ($task = File::exist($folder . 'task.php')) {
        include $task;
    }

    // Load user function(s) from the current shield folder if any
    if ($fn = File::exist($folder . 'index.php')) require $fn;

    // Detect relative asset path to the `.\lot\shield\*` folder
    if (Extend::exist('asset') && $assets = Asset::get(null, [])) {
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
                if ($path = File::exist(SHIELD . DS . $config->shield . DS . 'asset' . DS . $kk)) {
                    Asset::reset($kk)->set($path, $vv['stack']);
                }
            }
        }
    }

}, 0);