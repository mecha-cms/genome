<?php

// Wrap description data with paragraph tag(s) if needed
Hook::set('page.description', function($description) {
    if ($description && strpos($description, '</p>') === false) {
        return '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($description))) . '</p>';
    }
    return $description;
});

// Add CSS file to the `<head>` section…
Asset::set('css/document.min.css', 20);

// Add JS file to the `<body>` section…
Asset::set('js/document.min.js', 20);

// Create site navigation data to be used in template
$GLOBALS['menus'] = Get::pages()->is(function($v) {
    $f = PAGE . DS . extend('page')['path'];
    return $v !== $f . '.page' && $v !== $f . '.archive'; // Remove home page
})->map(function($v) use($url) {
    $v = new Page($v);
    $v->active = strpos($url->path . '/', '/' . $v->slug . '/') === 0; // Active state
    return $v;
});

// Create site trace data to be used in template
$traces = [];
$chops = explode('/', trim($url->path, '/'));
$k = "";
while ($chop = array_shift($chops)) {
    $k .= '/' . $chop;
    if ($v = File::exist([
        PAGE . $k . '.page',
        PAGE . $k . '.archive'
    ])) {
        $traces[] = new Page($v);
    }
}
$GLOBALS['traces'] = new Anemon($traces);