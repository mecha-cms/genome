<?php

define('PLUGIN', __DIR__);

$plugins = [];
$seeds = Lot::get(null, []);
foreach (g(PLUGIN . DS . 'lot' . DS . 'asset' . DS . '*', '{index__,index,__index}.php') as $v) {
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
    $f__ = PLUGIN . DS . 'lot' . DS . 'asset' . DS . $k . DS;
    $l__ = $f__ . 'lot' . DS . 'language' . DS;
    // back–end and front–end
    if ($i18n = File::exist($l__ . $config->language . '.txt')) {
        Language::set(From::yaml($i18n));
    }
    // back–end
    if ($config->type === false) {
        if ($i18n = File::exist($l__ . '___' . $config->language . '.txt')) {
            Language::set(From::yaml($i18n));
        }
    // front–end
    } else {
        if ($i18n = File::exist($l__ . $config->language . '__.txt')) {
            Language::set(From::yaml($i18n));
        }
    }
    // back–end and front–end
    if ($inc = File::exist($f__ . 'index.php')) {
        require $inc;
    }
    // back–end
    if ($config->type === false) {
        if ($inc = File::exist($f__ . '__index.php')) {
            Language::set(From::yaml($l__ . '__' . $config->language . '.txt'));
            require $inc;
        }
    // front–end
    } else {
        if ($inc = File::exist($f__ . 'index__.php')) {
            Language::set(From::yaml($l__ . $config->language . '__.txt'));
            require $inc;
        }
    }
}