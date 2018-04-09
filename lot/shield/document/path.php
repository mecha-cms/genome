<?php

$format = ['<span>%{0}%</span>', '<a href="%{1}%">%{0}%</a>'];

$a = explode('/', $url->path);
$b = __replace__($format[$site->is('home') ? 0 : 1], [$language->home, $url]);
$c = "";

array_pop($a); // remove the last path

while ($d = array_shift($a)) {
    $c .= '/' . $d;
    $f = PAGE . DS . $c;
    if (!$f = File::exist([
        $f . '.page',
        $f . '.archive'
    ])) {
        continue;
    }
    $d = Page::open($f)->get('title', To::title($d));
    $b .= ' / ' . __replace__($format[1], [$d, $url . $c]);
}

$b .= ' / ' . __replace__($format[0], [$page->title ?: $language->error]);

echo $b;