<?php

d(ENGINE . DS . 'kernel', function($w, $n) {
    $f = ENGINE . DS . 'plug' . DS . $n . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

File::$config['file_extension_allow'] = array_unique(array_merge(FONT_X, IMAGE_X, MEDIA_X, PACKAGE_X, SCRIPT_X));

Config::start();

$config = Genome\Config::start();
$i18n = Genome\I18N::start();

Session::start();

if ($fn = File::exist(SHIELD . DS . $config->shield . DS . 'engine' . DS . 'fire.php')) {
    require $fn;
}

r(EXTEND . DS . '*' . DS . 'engine', '{fire.php,fire__.php,__fire.php}');

Hook::fire('start');

Route::fire();