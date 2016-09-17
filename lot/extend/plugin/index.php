<?php

define('PLUGIN', __DIR__);

$plugins = [];
$seed = Seed::get(null, []);
foreach (g(PLUGIN . DS . 'lot' . DS . 'asset' . DS . '*', '{index.php,index__.php,__index.php}') as $v) {
    $d = Path::D($v);
    $n = Path::N($v);
    d($d . DS . 'engine' . DS . 'kernel', function($w, $n) use($d, $seed) {
        $f = $d . DS . 'engine' . DS . 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract($seed);
            require $f;
        }
    }, $seed);
    $plugins[Path::B($d)] = (float) File::open($d . DS . 'index.stack')->get(1, 10);
}

asort($plugins);

foreach ($plugins as $k => $v) {
    $f__ = PLUGIN . DS . $k . DS;
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