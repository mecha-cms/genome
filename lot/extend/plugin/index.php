<?php

define('PLUGIN', __DIR__ . DS . 'lot' . DS . 'worker');

call_user_func(function() {
    $plugins = [];
    $seeds = Lot::get(null, []);
    foreach (g(PLUGIN . DS . '*', '{index__,index}.php') as $v) {
        $plugins[$v] = (float) File::open(Path::D($v) . DS . 'index.stack')->get(0, 10);
    }
    asort($plugins);
    extract($seeds);
    foreach ($plugins as $k => $v) {
        $f = Path::D($k) . DS;
        $i18n = $f . 'lot' . DS . 'language' . DS;
        if ($l = File::exist([
            $i18n . $config->language . '.page',
            $i18n . 'en-us.page'
        ])) {
            $i18n = new Page($l, [], 'language');
            $fn = 'From::' . l($i18n->type);
            Language::set(is_callable($fn) ? call_user_func($fn, $i18n->content) : $i18n->content);
        }
        $f .= 'engine' . DS;
        d($f . 'kernel', function($w, $n) use($f, $seeds) {
            $f .= 'plug' . DS . $n . '.php';
            if (file_exists($f)) {
                extract($seeds);
                require $f;
            }
        }, $seeds);
    }
    foreach (array_keys($plugins) as $v) require $v;
});