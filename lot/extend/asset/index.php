<?php namespace fn;

function asset($content) {
    $content = str_replace('</head>', \Hook::fire('asset:head', [""], null, \Asset::class) . '</head>', $content);
    $content = str_replace('</body>', \Hook::fire('asset:body', [""], null, \Asset::class) . '</body>', $content);
    return $content;
}

\Hook::set('asset:head', function($content) {
    return $content . \Hook::fire('asset.css', [\Asset::css()], null, \Asset::class);
});

\Hook::set('asset:body', function($content) {
    return $content . \Hook::fire('asset.js', [\Asset::js()], null, \Asset::class);
});

\Hook::set('shield.yield', __NAMESPACE__ . "\\asset", 0);