<?php

Route::add('(:all)', function($path) use($config) {
    if ($folder = Folder::exist(POST . DS . $path)) {
        $posts = [];
        foreach (g($folder, 'txt') as $post) {
            $posts[] = new Post($post);
        }
        Seed::set('posts', $posts);
        Shield::attach('posts');
    } elseif ($file = File::exist(POST . DS . $path . '.txt')) {
        $post = new Post($file);
        $config->title = new Anemon([$config->title, $post->title], $config->separator);
        Seed::set('post', $post);
        Shield::attach('post');
    }
});