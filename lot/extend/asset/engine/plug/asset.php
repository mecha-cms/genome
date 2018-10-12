<?php

$state = Extend::state('asset', 'url');

foreach ([
    'css' => function($value, $key, $attr) use($state) {
        extract($value);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if ($path === false && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $href = $path === false ? $url : candy($state, [$url, file_exists($path) ? filemtime($path) : 0]);
        if (isset($attr['href']) && is_callable($attr['href'])) {
            $href = call_user_func($attr['href'], $href, $value, $key, $attr);
            unset($attr['href']);
        }
        return HTML::unite('link', false, extend(is_array($attr) ? $attr : [], [
            'href' => $href,
            'rel' => 'stylesheet'
        ]));
    },
    'js' => function($value, $key, $attr) use($state) {
        extract($value);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if ($path === false && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $src = $path === false ? $url : candy($state, [$url, file_exists($path) ? filemtime($path) : 0]);
        if (isset($attr['src']) && is_callable($attr['src'])) {
            $src = call_user_func($attr['src'], $src, $value, $key, $attr);
            unset($attr['src']);
        }
        return HTML::unite('script', "", extend(is_array($attr) ? $attr : [], [
            'src' => $src
        ]));
    }
] as $k => $v) {
    Asset::_('.' . $k, $v);
}

foreach (['gif', 'jpg', 'jpeg', 'png'] as $v) {
    Asset::_('.' . $v, function($value, $key, $attr) use($state) {
        extract($value);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if ($path === false && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $src = $path === false ? $url : candy($state, [$url, file_exists($path) ? filemtime($path) : 0]);
        if (isset($attr['src']) && is_callable($attr['src'])) {
            $src = call_user_func($attr['src'], $src, $value, $key, $attr);
            unset($attr['src']);
        }
        return HTML::unite('img', false, extend(is_array($attr) ? $attr : [], [
            'src' => $src
        ]));
    });
}