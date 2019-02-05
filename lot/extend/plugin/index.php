<?php

define('PLUGIN', __DIR__ . DS . 'lot' . DS . 'worker');

call_user_func(function() {
    $plugins = [];
    $seeds = Lot::get();
    foreach (g(PLUGIN . DS . '*', 'index.php') as $v) {
        $plugins[$v] = (float) File::open(Path::D($v) . DS . 'stack.data')->get(0, 10);
    }
    asort($plugins);
    extract($seeds, EXTR_SKIP);
    Config::set('plugin[]', $plugins);
    $files = [];
    foreach ($plugins as $k => $v) {
        $f = dirname($k) . DS;
        $ff = $f . 'lot' . DS . 'language' . DS;
        if ($ff = File::exist([
            $ff . $config->language . '.page',
            $ff . 'en-us.page'
        ])) {
            $files[] = $ff;
        }
        $f .= 'engine' . DS;
        d($f . 'kernel', function($w, $n) use($f, $seeds) {
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
            $fn = 'From::' . Page::apart($file, 'type', "");
            $content = Page::apart($file, 'content', "");
            $out = extend($out, is_callable($fn) ? (array) call_user_func($fn, $content) : []);
        }
        return $out;
    }) ?? []);
    foreach (array_keys($plugins) as $v) {
        if ($k = File::exist(dirname($v) . DS . 'task.php')) {
            include $k;
        }
        require $v;
    }
});