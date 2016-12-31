<?php

define('PLUGIN', __DIR__);

$plugins = [];
$seeds = Seed::get(null, []);
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
    if ($inc = File::exist($f__ . 'index.php')) {
        Language::set(From::yaml($l__ . $config->language . '.txt'));
        require $inc;
    }
    if ($config->page_type === false) {
        if ($inc = File::exist($f__ . '__index.php')) {
            Language::set(From::yaml($l__ . '__' . $config->language . '.txt'));
            require $inc;
        }
    } else {
        if ($inc = File::exist($f . 'index__.php')) {
            Language::set(From::yaml($l__ . $config->language . '__.txt'));
            require $inc;
        }
    }
}