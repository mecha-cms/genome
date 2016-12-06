<?php

Route::set('%', function($path) use($config) {
    $x = 'txt';
    if ($folder = Folder::exist(POST . DS . $path)) {
        $posts = [];
        foreach (g($folder, $x) as $post) {
            $posts[] = new Post($post);
        }
        Seed::set('posts', $posts);
        Shield::attach('posts');
    } elseif ($file = File::exist(POST . DS . $path . '.' . $x)) {
        $post = new Post($file);
        $config->title = new Anemon([$post->title, $config->title], $config->separator[0]);
        Seed::set('post', $post);
        Shield::attach('post');
    }
});