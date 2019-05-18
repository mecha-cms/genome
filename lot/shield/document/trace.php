<?php

$f = ['<span>%s</span>', '<a href="%2$s">%1$s</a>'];
$separator = $lot[0] ?? ' / ';

$chops = explode('/', trim($url->path, '/'));
$out = sprintf($f[$site->is('home') ? 0 : 1], $language->home, $url);
$path = "";

array_pop($chops); // Remove the last path

while ($chop = array_shift($chops)) {
    $path .= '/' . $chop;
    if (!$v = File::exist([
        PAGE . $path . '.page',
        PAGE . $path . '.archive'
    ])) {
        continue;
    }
    $v = page($v);
    $out .= $separator . sprintf($f[1], $v->title ?: $language->isError, $v->url);
}

$out .= $separator . sprintf($f[0], $page->title ?: $language->isError);

echo $out;