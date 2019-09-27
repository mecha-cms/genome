<?php

foreach ([
    'css' => function($value, $key) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $href = $path ? $url . '?v=' . (is_file($path) ? filemtime($path) : 0) : $url;
        if (isset($data['href']) && is_callable($data['href'])) {
            $href = fire($data['href'], [$href, $value, $key], null, Asset::class);
            unset($data['href']);
        }
        return new HTML(['link', false, array_replace_recursive($data, [
            'href' => $href,
            'rel' => 'stylesheet'
        ])]);
    },
    'js' => function($value, $key) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $src = $path ? $url . '?v=' . (is_file($path) ? filemtime($path) : 0) : $url;
        if (isset($data['src']) && is_callable($data['src'])) {
            $src = fire($data['src'], [$src, $value, $key], null, Asset::class);
            unset($data['src']);
        }
        return new HTML(['script', "", array_replace_recursive($data, [
            'src' => $src
        ])]);
    }
] as $k => $v) {
    Asset::_('.' . $k, $v);
}

foreach (['gif', 'jpg', 'jpeg', 'png'] as $v) {
    Asset::_('.' . $v, function($value, $key) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $src = $path ? $url . '?v=' . (is_file($path) ? filemtime($path) : 0) : $url;
        if (isset($data['src']) && is_callable($data['src'])) {
            $src = fire($data['src'], [$src, $value, $key], null, Asset::class);
            unset($data['src']);
        }
        return new HTML(['img', false, array_replace_recursive($data, [
            'src' => $src
        ])]);
    });
}

foreach (['script', 'style', 'template'] as $v) {
    Asset::_($v, function(string $content, float $stack = 10, array $data = []) use($v) {
        $c = Asset::class;
        $id = $data['id'] ?? $v . ':' . sprintf('%u', crc32($content));
        if (!isset(static::$lot[$c][0][':' . $v][$id])) {
            static::$lot[$c][1][':' . $v][$id] = [
                'content' => n(trim($content)),
                'data' => $data,
                'stack' => (float) $stack
            ];
        }
    });
}