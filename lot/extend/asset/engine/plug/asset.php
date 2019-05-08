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
        $link = new HTML;
        $link[0] = 'link';
        $link[1] = false;
        $link[2] = extend($data, [
            'href' => $href,
            'rel' => 'stylesheet'
        ]);
        return $link;
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
        $script = new HTML;
        $script[0] = 'script';
        $script[2] = extend($data, [
            'src' => $src
        ]);
        return $script;
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
        $img = new HTML;
        $img[0] = 'img';
        $img[1] = false;
        $img[2] = extend($data, [
            'src' => $src
        ]);
        return $img;
    });
}

foreach (['script', 'style', 'template'] as $v) {
    Asset::_($v, function(string $content, float $stack = 10, array $data = []) use($v) {
        $c = static::class;
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