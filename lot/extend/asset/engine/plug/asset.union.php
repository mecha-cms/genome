<?php

$state = Extend::state(Path::D(__DIR__, 2), 'url');

Asset\Union::plug('css', function($value, $key, $attr) use($state) {
    extract($value);
    $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
    if ($path === false && !$x) {
        return '<!-- ' . $key . ' -->';
    }
    return HTML::unite('link', false, Anemon::extend($attr, [
        'href' => $path === false ? $url : __replace__($state, [$url, File::T($path, 0)]),
        'rel' => 'stylesheet'
    ]));
});

Asset\Union::plug('js', function($value, $key, $attr) use($state) {
    extract($value);
    $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
    if ($path === false && !$x) {
        return '<!-- ' . $key . ' -->';
    }
    return HTML::unite('script', "", Anemon::extend($attr, [
        'src' => $path === false ? $url : __replace__($state, [$url, File::T($path, 0)])
    ]));
});

function fn_asset_image($value, $key, $attr) {
    extract($value);
    global $state;
    if ($path === false) {
        if (strpos($url, '://') !== false || strpos($url, '//') === 0) {
            return HTML::unite('img', false, Anemon::extend($attr, [
                'alt' => "",
                'src' => $url
            ]));
        }
        return '<!-- ' . $key . ' -->';
    }
    $z = getimagesize($path);
    return HTML::unite('img', false, Anemon::extend($attr, [
        'alt' => "",
        'src' => __replace__($state, [$url, File::T($path, 0)]),
        'width' => $z[0],
        'height' => $z[1]
    ]));
}

foreach (['gif', 'jpg', 'jpeg', 'png'] as $x) {
    Asset\Union::plug($x, 'fn_asset_image');
}