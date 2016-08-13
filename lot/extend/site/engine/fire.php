<?php

r(__DIR__ . DS . 'kernel', '*.php');

Route::add('(:all)', function($path) {
    if ($sheet = File::exist(SHEET . DS . To::path($path) . '.txt')) {
        $post = Genome\Sheet::open($sheet)->read();
        $post = new Genome\Sheet\Post($post);
        Shield::lot(['post' => $post])->attach('post');
    }
});