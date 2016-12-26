<?php

define('POST', PAGE . DS . 'post');

Route::set(['post/%/%i', 'post/%', 'post'], function($path = "", $offset = 1) use($config) {
    $folder = rtrim(POST . DS . To::path($path), DS);
    // from a file ...
    if ($file = File::exist([$folder . '.page', $folder . '.archive'])) {
        $page = new Page($file);
        Config::set('page.title', new Anemon([$page->title, $config->title], ' &ndash; '));
        Seed::set('page', $page);
        if ($files = Anemon::eat(Get::pages($folder, [], 1, 'slug'))->chunk(5, $offset - 1)) {
            $pages = [];
            foreach ($files as $file) {
                $pages[] = new Page($file['path']);
            }
            Seed::set([
                'pager' => null,
                'pages' => $pages
            ]);
            Shield::attach('pages-posts');
        }
        Shield::attach('page-post');
    // from a folder ...
    } else if (Is::D($folder)) {
        if ($file = File::exist([$folder . '.page', $folder . '.archive'])) {
            $page = new Page($file);
        } else {
            $page = false;
        }
        $pages = [];
        if ($files = Anemon::eat(Get::pages($folder, [], 1, 'slug'))->chunk(5, $offset - 1)) {
            foreach ($files as $file) {
                $pages[] = new Page($file['path']);
            }
        } else {
            Shield::abort();
        }
        Config::set('page.title', new Anemon($page ? [$page->title, $config->title] : [$config->title], ' &ndash; '));
        Seed::set([
            'pager' => null,
            'page' => $page,
            'pages' => $pages
        ]);
        Shield::attach('pages-posts');
    }
});