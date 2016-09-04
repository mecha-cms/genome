<?php

Route::add('(:all)', function($path) {
    if ($folder = Folder::exist(POST . DS . $path)) {
        $posts = [];
        foreach (glob($folder . DS . '*.txt') as $post) {
            $posts[] = new Post($post);
        }
        Seed::set('posts', $posts);
        Shield::attach('posts');
    } elseif ($file = File::exist(POST . DS . $path . '.txt')) {
        Seed::set('post', new Post($file));
        Shield::attach('post');
    }
});