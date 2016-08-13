<?php

d(ENGINE . DS . 'kernel', function($w, $n) {
    $f = ENGINE . DS . 'plug' . DS . $n . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

File::$config['file_extension_allow'] = array_unique(array_merge(FONT_X, IMAGE_X, MEDIA_X, PACKAGE_X, SCRIPT_X));

Session::start();
Config::start();

$config = new Genome\Config;
$language = new Genome\Language;
$url = new Genome\URL;

r(EXTEND . DS . '*' . DS . 'engine', '{fire.php,fire__.php,__fire.php}');

$f = SHIELD . DS . $config->shield . DS . 'engine' . DS;

if ($fn = File::exist($f .   'fire.php')) require $fn; // front-end and back-end
if ($fn = File::exist($f . 'fire__.php')) require $fn; // front-end
if ($fn = File::exist($f . '__fire.php')) require $fn; // back-end

function do_start() {
    Route::fire();
    Shield::abort();
}

Hook::set('start', 'do_start')->fire('start');