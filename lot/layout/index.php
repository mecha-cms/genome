<?php

// Wrap description data with paragraph tag(s) if needed
Hook::set('page.description', function($description) {
    if ($description && false === strpos($description, '</p>')) {
        return '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($description))) . '</p>';
    }
    return $description;
});

// Add CSS file to the `<head>` section…
Asset::set('css/layout.min.css', 20);

// Create site navigation data to be used in layout
$GLOBALS['links'] = map(Pages::from(PAGE)->is(function($v) use($state) {
    $folder = PAGE . strtr($state->path, '/', DS);
    return $v !== $folder . '.page' && $v !== $folder . '.archive'; // Remove home page
})->get(), function($v) use($url) {
    $v = new Page($v);
    $v->active = 0 === strpos($url->path . '/', '/' . $v->name . '/'); // Active state
    return $v;
});

// Create site trace data to be used in layout
$traces = [];
$chops = explode('/', trim($url->path, '/'));
$k = "";
while ($chop = array_shift($chops)) {
    $k .= '/' . $chop;
    if ($v = File::exist([
        PAGE . $k . '.page',
        PAGE . $k . '.archive'
    ])) {
        $traces[] = $v;
    }
}
$GLOBALS['traces'] = new Pages($traces);