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
    function union() {
        global $config, $url;
        $folder = PAGE . DS . $url->path;
        $i = $url->i ?: 1;
        if ($path = \File::exist([
            $folder . DS . $i . '.page',
            $folder . DS . $i . '.archive',
            $folder . '.page',
            $folder . '.archive'
        ])) {
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
        \Hook::set('on.ready', __NAMESPACE__ . "\\union", 0);
        \Hook::set('page.css', __NAMESPACE__ . "\\css", 2);
        \Hook::set('page.js', __NAMESPACE__ . "\\js", 2);
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