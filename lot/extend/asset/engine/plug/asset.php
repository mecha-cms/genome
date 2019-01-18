<?php

$state = new State(['state' => Extend::state('asset')]);

foreach ([
    'css' => function($value, $key, $attr) use($state) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if ($path === false && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $href = $path === false ? $url : candy($state->state['url'], [$url, file_exists($path) ? filemtime($path) : 0]);
        if (isset($attr['href']) && is_callable($attr['href'])) {
            $href = fn($attr['href'], [$href, $value, $key, $attr], $state, Asset::class);
            unset($attr['href']);
        }
        return HTML::unite('link', false, extend($attr, [
            'href' => $href,
            'rel' => 'stylesheet'
        ]));
    },
    'js' => function($value, $key, $attr) use($state) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if ($path === false && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $src = $path === false ? $url : candy($state->state['url'], [$url, file_exists($path) ? filemtime($path) : 0]);
        if (isset($attr['src']) && is_callable($attr['src'])) {
            $src = fn($attr['src'], [$src, $value, $key, $attr], $state, Asset::class);
            unset($attr['src']);
        }
        return HTML::unite('script', "", extend($attr, [
            'src' => $src
        ]));
    }
] as $k => $v) {
    Asset::_('.' . $k, $v);
}

foreach (['gif', 'jpg', 'jpeg', 'png'] as $v) {
    Asset::_('.' . $v, function($value, $key, $attr) use($state) {
        extract($value, EXTR_SKIP);
        $x = strpos($url, '://') !== false || strpos($url, '//') === 0;
        if ($path === false && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $src = $path === false ? $url : candy($state->state['url'], [$url, file_exists($path) ? filemtime($path) : 0]);
        if (isset($attr['src']) && is_callable($attr['src'])) {
            $src = fn($attr['src'], [$src, $value, $key, $attr], $state, Asset::class);
            unset($attr['src']);
        }
        return HTML::unite('img', false, extend($attr, [
            'src' => $src
        ]));
    });
}

foreach (['script', 'style'] as $v) {
    Asset::_($v, function(string $content, float $stack = null, array $data = []) use($v) {
        $c = static::class;
        $id = $data['id'] ?? $v . ':' . sprintf('%u', crc32($content));
        if (!isset(static::$lot[$c][0][':' . $v][$id])) {
            static::$lot[$c][1][':' . $v][$id] = [
                'content' => trim($content),
                'id' => $id,
                'data' => $data,
                'stack' => (float) ($stack ?? 10)
            ];
        }
        return new static;
    });
}