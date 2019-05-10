<?php

// Prepare current shield state
$GLOBALS['state'] = $state = new State;

require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'content.php';

require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'config.php';
require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'content.php';
require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'hook.php';
require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'language.php';

// Load current shield state if any
$folder = Content::$config['folder'] . DS;
if ($f = File::exist($folder . 'state' . DS . 'config.php')) {
    $GLOBALS['state'] = $state = new State($f);
}

// Run shield task if any
if ($task = File::exist($folder . 'task.php')) {
    include $task;
}

// Load user function(s) from the current shield folder if any
if ($fn = File::exist($folder . 'index.php')) {
    call_user_func(function() use($fn) {
        extract($GLOBALS, EXTR_SKIP);
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
                Asset::let($kk);
                Asset::set($path, $vv['stack']);
            }
        }
    }
}