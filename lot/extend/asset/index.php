<?php namespace fn;

function asset($content) {
    $content = str_replace('</head>', \Hook::fire('asset:head', [""]) . '</head>', $content);
    $content = str_replace('</body>', \Hook::fire('asset:body', [""]) . '</body>', $content);
    return $content;
}

\Hook::set('asset:head', function($content) {
    return $content . \Hook::fire('asset.css', [\Asset::css()]);
});

\Hook::set('asset:body', function($content) {
    return $content . \Hook::fire('asset.js', [\Asset::js()]);
});

\Hook::set('shield.yield', __NAMESPACE__ . '\asset', 0);