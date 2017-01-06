<?php

$state = Extend::state(Path::D(__DIR__, 2), 'url');

Asset\Union::plug('css', function($value, $key, $attr) use($state) {
    extract($value);
    if ($path === false) {
        return '<!-- ' . $key . ' -->';
    }
    return HTML::unite('link', false, Anemon::extend($attr, [
        'href' => __replace__($state, [$url, File::T($path)]),
        'rel' => 'stylesheet'
    ]));
});

Asset\Union::plug('js', function($value, $key, $attr) use($state) {
    extract($value);
    if ($path === false) {
        return '<!-- ' . $key . ' -->';
    }
    return HTML::unite('script', "", Anemon::extend($attr, [
        'src' => __replace__($state, [$url, File::T($path)])
    ]));
});

function fn_asset_image($value, $key, $attr) {
    extract($value);
    global $state;
    if ($path === false) {
        return '<!-- ' . $key . ' -->';
    }
    $z = getimagesize($path);
    return HTML::unite('img', false, Anemon::extend($attr, [
        'alt' => "",
        'src' => __replace__($state, [$url, File::T($path)]),
        'width' => $z[0],
        'height' => $z[1]
    ]));
}

foreach (['gif', 'jpg', 'jpeg', 'png'] as $x) {
    Asset\Union::plug($x, 'fn_asset_image');
}