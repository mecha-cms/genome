<?php

define('PLUGIN', __DIR__ . DS . 'lot' . DS . 'worker');

call_user_func(function() {
    $plugins = [];
    $seeds = Lot::get();
    foreach (glob(PLUGIN . DS . '*' . DS . 'index.php', GLOB_NOSORT) as $v) {
        $b = basename($d = dirname($v));
        $plugins[$v] = content($d . DS . $b) ?: $b;
    }
    // Sort by name
    natsort($plugins);
    extract($seeds, EXTR_SKIP);
    Config::set('plugin[]', $plugins = array_keys($plugins));
    $files = [];
    foreach ($plugins as $v) {
        $f = dirname($v) . DS;
        $ff = $f . 'lot' . DS . 'language' . DS;
        if ($ff = File::exist([
            $ff . $config->language . '.page',
            $ff . 'en-us.page'
        ])) {
            $files[] = $ff;
        }
        $f .= 'engine' . DS;
        d($f . 'kernel', function($c, $n) use($f, $seeds) {
            $f .= 'plug' . DS . $n . '.php';
            if (file_exists($f)) {
                extract($seeds, EXTR_SKIP);
                require $f;
            }
        }, $seeds);
    }
    // Load plugin(s)’ language…
    Language::set(Cache::hit($files, function($files): array {
        $out = [];
        foreach ($files as $file) {
            $file = file_get_contents($file);
            $fn = 'From::' . Page::apart($file, 'type', "");
            $content = Page::apart($file, 'content', "");
            $out = extend($out, is_callable($fn) ? (array) call_user_func($fn, $content) : []);
        }
        return $out;
    }) ?? []);
    foreach ($plugins as $v) {
        if ($k = File::exist(dirname($v) . DS . 'task.php')) {
            include $k;
        }
        require $v;
    }
});