<?php

function fn_asset($content) {
    $lot = ["", Lot::get(null, [])];
    $content = str_ireplace('</head>', Hook::NS('asset.top', $lot) . '</head>', $content);
    $content = str_ireplace('</body>', Hook::NS('asset.bottom', $lot) . '</body>', $content);
    return $content;
}

Hook::set('asset.top', function($content) {
    return $content . Asset::css();
});

Hook::set('asset.bottom', function($content) {
    return $content . Asset::js();
});

Hook::set('shield.input', 'fn_asset', 0);