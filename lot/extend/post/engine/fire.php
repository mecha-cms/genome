<?php

Route::add('(:all)', function($path) {
    if ($post = File::exist(POST . DS . $path . '.txt')) {
        $post = Genome\Sheet::open($post)->read();
        Seed::set('post', new Post($post));
        Shield::attach('post');
    }
});