<?php

Route::set(['%/%i', '%'], function($path, $offset = 1) use($config) {
    $folder = PAGE_POST . DS . To::path($path);
    // from a file ...
    if ($file = File::exist($folder . '.post')) {
        $page = new Post($file);
        if (Is::D($folder) && count(g($folder, 'data', "", false)) === 0) {
            $pages = [];
            if ($files = Anemon::eat(Get::pages($folder, [], 1, 'slug'))->chunk(5, $offset - 1)) {
                foreach ($files as $file) {
                    $pages[] = new Post($file['path']);
                }
            } else {
                Shield::abort();
            }
            Config::set('page.title', new Anemon([$page->title, $config->title], ' &ndash; '));
            Seed::set([
                'pages' => $pages,
                'page' => $page
            ]);
            Shield::attach('pages-posts');
        } else {
            $page = new Post($file);
            Config::set('page.title', new Anemon([$page->title, $config->title], ' &ndash; '));
            Seed::set('page', $page);
            Shield::attach('page-post');
        }
    // from a folder ...
    } else if (Is::D($folder)) {
        if ($file = File::exist($folder . '.post')) {
            $page = new Post($file);
        } else {
            $page = false;
        }
        $pages = [];
        if ($files = Anemon::eat(Get::pages($folder, [], 1, 'slug'))->chunk(5, $offset - 1)) {
            foreach ($files as $file) {
                $pages[] = new Post($file['path']);
            }
        } else {
            Shield::abort();
        }
        Config::set('page.title', new Anemon($page ? [$page->title, $config->title] : [$config->title], ' &ndash; '));
        Seed::set([
            'pages' => $pages,
            'page' => $page
        ]);
        Shield::attach('pages-posts');
    }
    return null;
});