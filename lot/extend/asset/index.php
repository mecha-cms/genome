<?php namespace fn;

function asset($content) {
    $content = \str_replace('</head>', \Hook::fire('asset:head', [""], null, \Asset::class) . '</head>', $content);
    $content = \str_replace('</body>', \Hook::fire('asset:body', [""], null, \Asset::class) . '</body>', $content);
    return $content;
}

\Hook::set('asset:head', function($content) {
    $a = \Hook::fire('asset.css', [\Asset::css()], null, \Asset::class);
    $b = "";
    $lot = \Asset::get();
    if (!empty($lot[':style'])) {
        foreach (\Anemon::eat($lot[':style'])->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v['content'])) {
                $b .= N . \HTML::unite('style', N . $v['content'] . N, $v['data']);
            }
        }
    }
    return $content . $a . $b . N; // Put inline CSS after remote CSS
});

\Hook::set('asset:body', function($content) {
    $js = \Hook::fire('asset.js', [\Asset::js()], null, \Asset::class);
    $script = $template = "";
    $lot = \Asset::get();
    if (!empty($lot[':script'])) {
        foreach (\Anemon::eat($lot[':script'])->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v['content'])) {
                $script .= N . \HTML::unite('script', N . $v['content'] . N, $v['data']);
            }
        }
    }
    if (!empty($lot[':template'])) {
        foreach (\Anemon::eat($lot[':template'])->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v['content'])) {
                $template .= N . \HTML::unite('template', N . DENT . \str_replace("\n", DENT . "\n", $v['content']) . N, $v['data']);
            }
        }
    }
    return $content . $template . $script . N; // Put inline JS after remote JS
});

\Hook::set('shield.yield', __NAMESPACE__ . "\\asset", 0);