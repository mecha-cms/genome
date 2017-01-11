<?php

define('PLUGIN', __DIR__);

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
    if ($i18n = File::exist($l__ . $l . '.txt')) {
        Language::set(From::yaml($i18n));
    }
    // front–end
    if ($i18n = File::exist($l__ . $l . '__.txt')) {
        Language::set(From::yaml($i18n));
    }
    // back–end and front–end
    if ($inc = File::exist($f__ . 'index.php')) {
        require $inc;
    }
    // front–end
    if ($inc = File::exist($f__ . 'index__.php')) {
        Language::set(From::yaml($l__ . $l . '__.txt'));
        require $inc;
    }
}