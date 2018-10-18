<?php

namespace fn\art {
    function css($content) {
        $content = trim($content);
        if ($content && strpos($content, '</style>') === false && strpos($content, '<link ') === false) {
            return '<style media="screen">' . N . $content . N . '</style>';
        }
        return $content;
    }
    function js($content) {
        $content = trim($content);
        if ($content && strpos($content, '</script>') === false && strpos($content, '<script ') === false) {
            return '<script>' . N . $content . N . '</script>';
        }
        return $content;
    }
    function classes() {
        global $site;
        if ($path = $site->is('page')) {
            extract(\Page::open($path)->get([
                'css' => null,
                'js' => null
            ]));
            \Config::set('has', [
                'css' => !!$css,
                'js' => !!$js
            ]);
            \Config::set('is.art', $css || $js);
            \Config::set('not.art', !$css && !$js);
        }
    }
    if (!\HTTP::is('get', 'art') || \HTTP::get('art')) {
        \Hook::set('on.ready', __NAMESPACE__ . '\classes', 0);
        \Hook::set('page.css', __NAMESPACE__ . '\css', 2);
        \Hook::set('page.js', __NAMESPACE__ . '\js', 2);
        \Hook::set('shield.yield', __NAMESPACE__, 1);
    }
}

namespace fn {
    function art($content) {
        if (!$page = \Lot::get('page')) {
            return $content;
        }
        // Append custom CSS before `</head>`…
        $content = str_replace('</head>', $page->css . '</head>', $content);
        // Append custom JS before `</body>`…
        $content = str_replace('</body>', $page->js . '</body>', $content);
        return $content;
    }
}