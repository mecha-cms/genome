<?php

Block::set('e', function($content) {
    return Block::replace('e', function($content) {
        ob_start();
        eval($content);
        return ob_get_clean();
    }, $content);
});