<?php

$state = Extend::state('asset', 'url');

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

foreach (['gif', 'jpg', 'jpeg', 'png'] as $x) {
    Asset\Union::plug($x, function($value, $key, $attr) use($state) {
        extract($value);
        if ($path === false) {
            if (strpos($url, '://') !== false || strpos($url, '//') === 0) {
                return HTML::unite('img', false, Anemon::extend($attr, [
                    'alt' => "",
                    'src' => $url
                ]));
            }
            return '<!-- ' . $key . ' -->';
        }
        return HTML::unite('img', false, Anemon::extend($attr, [
            'alt' => "",
            'src' => __replace__($state, [$url, File::T($path, 0)])
        ]));
    });
}