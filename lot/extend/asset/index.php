<?php namespace fn;

function asset($content) {
    $content = \str_replace('</head>', \Hook::fire('asset:head', [""], null, \Asset::class) . '</head>', $content);
    $content = \str_replace('</body>', \Hook::fire('asset:body', [""], null, \Asset::class) . '</body>', $content);
    return $content;
}

\Hook::set('asset:head', function($content) {
    $css = \Hook::fire('asset.css', [\Asset::join('css')], null, \Asset::class);
    $style = "";
    $lot = \Asset::get();
    if (!empty($lot[':style'])) {
        foreach (\Anemon::eat($lot[':style'])->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v['content'])) {
                $s = new \HTML;
                $s[0] = 'style';
                $s[1] = $v['content'];
                $s[2] = $v['data'];
                $style .= $s;
            }
        }
    }
    $style = \Hook::fire('asset:style', [$style], null, \Asset::class);
    return $content . $css . $style; // Put inline CSS after remote CSS
});

\Hook::set('asset:body', function($content) {
    $js = \Hook::fire('asset.js', [\Asset::join('js')], null, \Asset::class);
    $script = $template = "";
    $lot = \Asset::get();
    if (!empty($lot[':script'])) {
        foreach (\Anemon::eat($lot[':script'])->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v['content'])) {
                $s = new \HTML;
                $s[0] = 'script';
                $s[1] = $v['content'];
                $s[2] = $v['data'];
                $script .= $s;
            }
        }
    }
    if (!empty($lot[':template'])) {
        foreach (\Anemon::eat($lot[':template'])->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v['content'])) {
                $t = new \HTML;
                $t[0] = 'template';
                $t[1] = $v['content'];
                $t[2] = $v['data'];
                $template .= $t;
            }
        }
    }
    $script = \Hook::fire('asset:script', [$script], null, \Asset::class);
    $template = \Hook::fire('asset:template', [$template], null, \Asset::class);
    return $content . $template . $js . $script; // Put inline JS after remote JS
});

\Hook::set('content', __NAMESPACE__ . "\\asset", 0);