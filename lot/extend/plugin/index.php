<?php

define('PLUGIN', __DIR__);

call_user_func(function() {
    $plugins = [];
    $seeds = Lot::get(null, []);
    foreach (g(PLUGIN . DS . 'lot' . DS . 'worker' . DS . '*', '{index__,index}.php') as $v) {
        $d = Path::D($v);
        $n = Path::N($v);
        d($d . DS . 'engine' . DS . 'kernel', function($w, $n) use($d, $seeds) {
            $f = $d . DS . 'engine' . DS . 'plug' . DS . $n . '.php';
            if (file_exists($f)) {
                extract($seeds);
                require $f;
            }
        }, $seeds);
        $plugins[Path::B($d)] = (float) File::open($d . DS . 'index.stack')->get(0, 10);
    }
    asort($plugins);
    extract($seeds);
    foreach ($plugins as $k => $v) {
        $f__ = PLUGIN . DS . 'lot' . DS . 'worker' . DS . $k . DS;
        $l__ = $f__ . 'lot' . DS . 'language' . DS;
        $l = $config->language;
        // back–end and front–end
        if ($i18n = File::exist($l__ . $l . '.page')) {
            $i18n = new Page($i18n, [], 'language');
            $fn = 'From::' . l($i18n->type);
            Language::set(is_callable($fn) ? call_user_func($fn, $i18n->content) : $i18n->content);
        }
        // front–end
        if ($i18n = File::exist($l__ . $l . '__.page')) {
            $i18n = new Page($i18n, [], 'language');
            $fn = 'From::' . l($i18n->type);
            Language::set(is_callable($fn) ? call_user_func($fn, $i18n->content) : $i18n->content);
        }
        // back–end and front–end
        if ($inc = File::exist($f__ . 'index.php')) {
            require $inc;
        }
        // front–end
        if ($inc = File::exist($f__ . 'index__.php')) {
            require $inc;
        }
    }
});