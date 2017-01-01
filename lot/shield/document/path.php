<?php

$path = $url->path;
$format = ['<span>%1$s</span>', '<a href="%2$s">%1$s</a>'];

$a = explode('/', $path);
$b = sprintf($format[$url->path === "" || $path === $config->slug ? 0 : 1], $language->home, $url);
$c = "";

while ($d = array_shift($a)) {
    $c .= '/' . $d;
    $d = is_numeric($d) ? sprintf($format[0], $language->page . ' ' . $d) : To::title($d);
    $d = Page::open(PAGE . DS . $c . '.page')->get('title', $d);
    $b .= ' / ' . sprintf($format['/' . $path === $c ? 0 : 1], $d, $url . $c);
}

echo $b;