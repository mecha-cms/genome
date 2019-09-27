<?php

// Wrap description data with paragraph tag(s) if needed
Hook::set('page.description', function($description) {
    if ($description && strpos($description, '</p>') === false) {
        return '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($description))) . '</p>';
    }
    return $description;
});

// Add CSS file to the `<head>` section…
Asset::set('css/log.min.css', 20);

// Create site navigation data to be used in content
$GLOBALS['links'] = map(Pages::from(PAGE)->is(function($v) {
    $folder = PAGE . DS . state('x.page./');
    return $v !== $folder . '.page' && $v !== $folder . '.archive'; // Remove home page
})->get(), function($v) use($url) {
    $v = new Page($v);
    $v->active = strpos($url->path . '/', '/' . $v->name . '/') === 0; // Active state
    return $v;
});

// Create site trace data to be used in content
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