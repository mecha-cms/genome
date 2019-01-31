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
    foreach ($plugins as $k => $v) {
        $f = Path::D($k) . DS;
        $ff = $f . 'lot' . DS . 'language' . DS;
        if ($ff = File::exist([
            $ff . $config->language . '.page',
            $ff . 'en-us.page'
        ])) {
            // Load plugin(s)’ language…
            Language::set(Cache::of($ff, function() use($ff) {
                $fn = 'From::' . Page::apart($ff, 'type', "");
                $content = Page::apart($ff, 'content', "");
                return is_callable($fn) ? call_user_func($fn, $content) : [];
            }, filemtime($ff), []));
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
    foreach (array_keys($plugins) as $v) {
        if ($k = File::exist(dirname($v) . DS . 'task.php')) {
            include $k;
        }
        require $v;
    }
});