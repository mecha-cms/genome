<?php

Block::set('url', function($content) {
    $state = Extend::state('block', 'union');
    $o = $state[1][0][0]; // `[[`
    $url = __url__();
    if (strpos($content, $o . 'url.') !== false) {
        foreach (array_keys($url) as $v) {
            $content = Block::replace('url.' . $v, $url[$v], $content);
        }
    }
    return Block::replace('url', $url['url'], $content);
});