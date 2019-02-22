<?php

$state = new State(['state' => Extend::state('asset')]);

foreach ([
    'css' => function($value, $key, $data) use($state) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if (!isset($path) && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $href = isset($path) ? candy($state->state['url'], [$url, is_file($path) ? filemtime($path) : 0]) : $url;
        if (isset($data['href']) && is_callable($data['href'])) {
            $href = fn($data['href'], [$href, $value, $key, $data], $state, Asset::class);
            unset($data['href']);
        }
        return HTML::unite('link', false, extend($data, [
            'href' => $href,
            'rel' => 'stylesheet'
        ]));
    },
    'js' => function($value, $key, $data) use($state) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if (!isset($path) && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $src = isset($path) ? candy($state->state['url'], [$url, is_file($path) ? filemtime($path) : 0]) : $url;
        if (isset($data['src']) && is_callable($data['src'])) {
            $src = fn($data['src'], [$src, $value, $key, $data], $state, Asset::class);
            unset($data['src']);
        }
        return HTML::unite('script', "", extend($data, [
            'src' => $src
        ]));
    }
] as $k => $v) {
    Asset::_('.' . $k, $v);
}

foreach (['gif', 'jpg', 'jpeg', 'png'] as $v) {
    Asset::_('.' . $v, function($value, $key, $data) use($state) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if (!isset($path) && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $src = isset($path) ? candy($state->state['url'], [$url, is_file($path) ? filemtime($path) : 0]) : $url;
        if (isset($data['src']) && is_callable($data['src'])) {
            $src = fn($data['src'], [$src, $value, $key, $data], $state, Asset::class);
            unset($data['src']);
        }
        return HTML::unite('img', false, extend($data, [
            'src' => $src
        ]));
    });
}

foreach (['script', 'style', 'template'] as $v) {
    Asset::_($v, function(string $content, float $stack = null, array $data = []) use($v) {
        $c = static::class;
        $id = $data['id'] ?? $v . ':' . sprintf('%u', crc32($content));
        if (!isset(static::$lot[$c][0][':' . $v][$id])) {
            static::$lot[$c][1][':' . $v][$id] = [
                'content' => n(trim($content)),
                'data' => $data,
                'stack' => (float) ($stack ?? 10)
            ];
        }
    });
}